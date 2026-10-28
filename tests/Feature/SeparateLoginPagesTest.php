<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Admin;
use App\Models\UserApiDomain;
use App\Models\CarePartnerRegistrationRequest;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\UserArticle;
use App\Models\UserBlog;
use App\Models\UserAboutUsPage;
use App\Models\UserCmsPage;
use App\Models\UserFaq;
use App\Models\UserGalleryImage;
use App\Models\UserNews;
use App\Models\UserReview;
use App\Models\Service;
use App\Models\Category;
use App\Models\UserService;
use App\Models\UserSliderItem;
use App\Models\UserSmtpSetting;
use App\Models\UserSocialMediaLink;
use App\Models\UserWebsiteSetting;
use App\Models\UserVideo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Tests\TestCase;

class SeparateLoginPagesTest extends TestCase
{
    public function test_admin_and_care_partner_login_pages_are_separate(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Care that feels')
            ->assertSee('href="'.route('login').'"', false)
            ->assertSee('Existing user login')
            ->assertSee('Website only')
            ->assertSee('Listing only')
            ->assertSee('Website + MediCare listing')
            ->assertSee('API & backend integration')
            ->assertSee('Manage your digital presence from the panel.')
            ->assertSee('Social profile links')
            ->assertSee('YouTube videos')
            ->assertSee('Email configuration')
            ->assertSee('WhatsApp contact')
            ->assertSee('Appointment management')
            ->assertSee('Coordinate your care team')
            ->assertSee('Track every visit')
            ->assertSee('patients cannot book online through this panel yet')
            ->assertSee('bulk WhatsApp broadcasts are not currently included')
            ->assertSee('Contact for pricing')
            ->assertSee('> Login</a>', false)
            ->assertSee('href="'.route('care-partner-registration.create').'"', false)
            ->assertSee('href="#directory"', false)
            ->assertSee('href="#plans"', false)
            ->assertSee('href="#contact"', false)
            ->assertSee('Active doctors')
            ->assertSee('Active hospitals')
            ->assertSee('data-plan-card', false)
            ->assertSee('aria-current="true"', false)
            ->assertDontSee('Sample Family Clinic')
            ->assertDontSee('Static demo content')
            ->assertSee('Care partner support')
            ->assertSee('Contact MediCare')
            ->assertSee('Send message')
            ->assertSee('Sample Branch One')
            ->assertSee('Sample Branch Two')
            ->assertSee('Demo address only')
            ->assertSee('These are fictional sample addresses')
            ->assertDontSee('Add official phone number');

        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Administrator sign in')
            ->assertSee('/admin/logins');

        $this->get('/login')
            ->assertOk()
            ->assertSee('Welcome back')
            ->assertSee('name="_token"', false)
            ->assertSee('name="username"', false);

        $this->get('/user/login')->assertRedirect('/login');
        $this->assertSame(url('/login'), route('user.logins'));
    }

    public function test_registration_form_prefills_the_selected_plan_and_stores_a_pending_request(): void
    {
        $practiceName = 'Test Clinic '.Str::uuid();
        $email = 'registration-'.Str::uuid().'@example.test';

        $this->get(route('care-partner-registration.create', ['plan' => 'api_integration']))
            ->assertOk()
            ->assertSee('Care partner registration')
            ->assertSee('<option value="api_integration" selected', false)
            ->assertSee('data-step="4"', false)
            ->assertSee('API token and domain binding setup')
            ->assertDontSee('Website launch offer')
            ->assertDontSee('instagram_url')
            ->assertDontSee('youtube_url');

        $this->post(route('care-partner-registration.store'), [
            'plan' => 'api_integration',
            'practice_type' => 'clinic',
            'practice_name' => $practiceName,
            'specialty' => 'Family medicine',
            'website_url' => 'https://clinic.example.test',
            'address' => '12 Test Road',
            'city' => 'Test City',
            'state' => 'Test State',
            'postal_code' => '123456',
            'contact_name' => 'Test Contact',
            'email' => $email,
            'phone' => '+911234567890',
            'message' => 'Need API integration support',
            'consent' => '1',
        ])->assertRedirect(route('care-partner-registration.create'))
            ->assertSessionHas('registration_submitted', true);

        $registration = CarePartnerRegistrationRequest::where('email', $email)->firstOrFail();
        try {
            $this->assertDatabaseHas('care_partner_registration_requests', [
                'id' => $registration->id,
                'plan' => 'api_integration',
                'practice_type' => 'clinic',
                'practice_name' => $practiceName,
                'contact_name' => 'Test Contact',
                'status' => 'pending',
            ]);
            $this->assertNotNull($registration->consented_at);
        } finally {
            $registration->delete();
        }
    }

    public function test_registration_rejects_invalid_plan_and_missing_required_details(): void
    {
        $this->from(route('care-partner-registration.create'))
            ->post(route('care-partner-registration.store'), [
                'plan' => 'made_up_plan',
            ])
            ->assertRedirect(route('care-partner-registration.create'))
            ->assertSessionHasErrors(['plan', 'practice_type', 'practice_name', 'contact_name', 'email', 'consent']);
    }

    public function test_contact_form_saves_enquiry_for_admin_inbox_and_admin_can_update_status(): void
    {
        $email = 'contact-'.Str::uuid().'@example.test';

        $this->get('/')
            ->assertOk()
            ->assertSee('action="'.route('contact.store').'"', false)
            ->assertSee('name="subject"', false)
            ->assertSee('name="message"', false);

        $this->from(route('home').'#contact')
            ->post(route('contact.store'), [
                'name' => 'Website Enquiry',
                'email' => $email,
                'phone' => '+911234567890',
                'subject' => 'Website information',
                'message' => 'Please share website plan details.',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('contact_submitted', true);

        $contactMessage = \App\Models\ContactMessage::where('email', $email)->firstOrFail();
        try {
            $this->assertDatabaseHas('contact_messages', [
                'id' => $contactMessage->id,
                'name' => 'Website Enquiry',
                'subject' => 'Website information',
                'status' => 'new',
            ]);

            $this->get(route('admin.contact-messages.index'))
                ->assertRedirect(route('admin.login'));

            $admin = new Admin([
                'name' => 'Contact Inbox Test Admin',
                'email' => 'contact-inbox-admin@example.test',
                'password' => 'unused',
            ]);
            $admin->id = 1;

            $this->actingAs($admin, 'admin')
                ->get(route('admin.contact-messages.index', ['q' => 'Website Enquiry']))
                ->assertOk()
                ->assertSee('Website Enquiry')
                ->assertSee($email)
                ->assertSee('Please share website plan details.')
                ->assertSee('mailto:'.$email, false)
                ->assertSee('New');

            $this->actingAs($admin, 'admin')
                ->patch(route('admin.contact-messages.status', $contactMessage), [
                    'status' => 'contacted',
                ])
                ->assertRedirect(route('admin.contact-messages.index'))
                ->assertSessionHas('status', 'Contact message status updated.');

            $this->assertDatabaseHas('contact_messages', [
                'id' => $contactMessage->id,
                'status' => 'contacted',
            ]);
        } finally {
            $contactMessage->delete();
        }
    }

    public function test_admin_can_review_contact_and_update_registration_request_status(): void
    {
        $email = 'admin-request-'.Str::uuid().'@example.test';
        $registrationRequest = CarePartnerRegistrationRequest::create([
            'plan' => 'website_listing',
            'practice_type' => 'clinic',
            'practice_name' => 'Admin Review Clinic',
            'specialty' => 'Primary care',
            'website_url' => 'https://clinic.example.test',
            'address' => '20 Clinic Road',
            'city' => 'Example City',
            'state' => 'Example State',
            'postal_code' => '123456',
            'contact_name' => 'Clinic Contact',
            'email' => $email,
            'phone' => '+911234567890',
            'message' => 'Please call after 10 AM.',
            'consented_at' => now(),
            'status' => 'pending',
        ]);

        try {
            $this->get(route('admin.registration-requests.index'))
                ->assertRedirect(route('admin.login'));

            $admin = new Admin([
                'name' => 'Registration Test Admin',
                'email' => 'registration-admin@example.test',
                'password' => 'unused',
            ]);
            $admin->id = 1;

            $this->actingAs($admin, 'admin')
                ->get(route('admin.registration-requests.index'))
                ->assertOk()
                ->assertSee('Registration requests')
                ->assertSee('Admin Review Clinic')
                ->assertSee($email)
                ->assertSee('Website + MediCare listing');

            $this->actingAs($admin, 'admin')
                ->get(route('admin.registration-requests.show', $registrationRequest))
                ->assertOk()
                ->assertSee('Clinic Contact')
                ->assertSee('Please call after 10 AM.')
                ->assertSee('mailto:'.$email, false)
                ->assertSee('tel:+911234567890', false)
                ->assertSee('https://wa.me/911234567890', false);

            $this->actingAs($admin, 'admin')
                ->patch(route('admin.registration-requests.status', $registrationRequest), [
                    'status' => 'contacted',
                ])
                ->assertRedirect(route('admin.registration-requests.show', $registrationRequest))
                ->assertSessionHas('status', 'Registration request status updated.');

            $this->assertDatabaseHas('care_partner_registration_requests', [
                'id' => $registrationRequest->id,
                'status' => 'contacted',
            ]);
        } finally {
            $registrationRequest->delete();
        }
    }

    public function test_care_partner_can_schedule_search_update_and_track_appointments(): void
    {
        $user = User::create([
            'name' => 'Appointment Doctor',
            'username' => Str::lower(Str::random(8)),
            'role' => 2,
            'email' => 'appointment-'.Str::uuid().'@example.test',
            'password' => Str::random(32),
        ]);
        $appointmentAt = now()->addDays(3)->startOfHour();

        try {
            $this->actingAs($user, 'user')
                ->get(route('user.appointments.index'))
                ->assertOk()
                ->assertSee('Appointments')
                ->assertSee('Schedule an appointment')
                    ->assertSee('Appointment register')
                    ->assertSee('name="appointment_at"', false)
                    ->assertSee('min="'.now()->addMinute()->startOfMinute()->format('Y-m-d\TH:i').'"', false);

                $this->from(route('user.appointments.index'))
                    ->actingAs($user, 'user')
                    ->post(route('user.appointments.store'), [
                        'patient_name' => 'Past Patient',
                        'phone' => '+911234567899',
                        'appointment_at' => now()->subMinutes(5)->format('Y-m-d\TH:i'),
                        'duration_minutes' => 30,
                        'reason' => 'Past appointment attempt',
                    ])
                    ->assertRedirect(route('user.appointments.index'))
                    ->assertSessionHasErrors('appointment_at');

            $this->actingAs($user, 'user')
                ->post(route('user.appointments.store'), [
                    'patient_name' => 'Sam Patient',
                    'phone' => '+911234567890',
                    'email' => 'sam.patient@example.test',
                    'appointment_at' => $appointmentAt->format('Y-m-d\TH:i'),
                    'duration_minutes' => 30,
                    'reason' => 'Follow-up consultation',
                    'notes' => 'Bring previous reports.',
                ])
                ->assertRedirect(route('user.appointments.index'))
                ->assertSessionHas('status', 'Appointment scheduled successfully.');

            $appointment = $user->appointments()->firstOrFail();
            $this->assertSame('scheduled', $appointment->status);

            $this->from(route('user.appointments.index'))
                ->actingAs($user, 'user')
                ->put(route('user.appointments.update', $appointment), [
                    'patient_name' => 'Sam Patient',
                    'phone' => '+911234567890',
                    'email' => 'sam.patient@example.test',
                    'appointment_at' => $appointmentAt->format('Y-m-d\TH:i'),
                    'duration_minutes' => 30,
                    'reason' => 'Follow-up consultation',
                    'notes' => 'Bring previous reports.',
                    'status' => 'confirmed',
                ])
                ->assertRedirect(route('user.appointments.index'))
                ->assertSessionHas('status', 'Appointment updated successfully.');

            $this->from(route('user.appointments.index'))
                ->actingAs($user, 'user')
                ->post(route('user.appointments.store'), [
                    'patient_name' => 'Overlap Patient',
                    'phone' => '+911234567891',
                    'appointment_at' => $appointmentAt->copy()->addMinutes(15)->format('Y-m-d\TH:i'),
                    'duration_minutes' => 30,
                    'reason' => 'Overlapping visit',
                ])
                ->assertRedirect(route('user.appointments.index'))
                ->assertSessionHasErrors('appointment_at');

            $this->actingAs($user, 'user')
                ->get(route('user.appointments.index', ['q' => 'Sam', 'status' => 'confirmed']))
                ->assertOk()
                ->assertSee('Sam Patient')
                ->assertSee('Confirmed')
                ->assertSee('Bring previous reports.');

            $this->actingAs($user, 'user')
                ->delete(route('user.appointments.destroy', $appointment))
                ->assertRedirect(route('user.appointments.index'))
                ->assertSessionHas('status', 'Appointment deleted successfully.');

            $this->assertDatabaseMissing('user_appointments', ['id' => $appointment->id]);
        } finally {
            $user->delete();
        }
    }

    public function test_care_partners_cannot_view_or_modify_another_users_appointments(): void
    {
        $owner = User::create([
            'name' => 'Appointment Owner',
            'username' => Str::lower(Str::random(8)),
            'role' => 2,
            'email' => 'appointment-owner-'.Str::uuid().'@example.test',
            'password' => Str::random(32),
        ]);
        $otherUser = User::create([
            'name' => 'Other Doctor',
            'username' => Str::lower(Str::random(8)),
            'role' => 2,
            'email' => 'appointment-other-'.Str::uuid().'@example.test',
            'password' => Str::random(32),
        ]);
        $appointment = $owner->appointments()->create([
            'patient_name' => 'Private Patient',
            'phone' => '+911234567890',
            'appointment_at' => now()->addDays(2),
            'duration_minutes' => 30,
            'reason' => 'Private appointment',
            'status' => 'scheduled',
        ]);

        try {
            $this->actingAs($otherUser, 'user')
                ->get(route('user.appointments.index'))
                ->assertOk()
                ->assertDontSee('Private Patient');

            $this->actingAs($otherUser, 'user')
                ->put(route('user.appointments.update', $appointment), [
                    'patient_name' => 'Changed Patient',
                    'phone' => '+911234567890',
                    'appointment_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
                    'duration_minutes' => 30,
                    'reason' => 'Changed reason',
                    'status' => 'confirmed',
                ])
                ->assertNotFound();

            $this->actingAs($otherUser, 'user')
                ->delete(route('user.appointments.destroy', $appointment))
                ->assertNotFound();

            $this->assertSame('Private Patient', $appointment->fresh()->patient_name);
            $this->assertDatabaseHas('user_appointments', [
                'id' => $appointment->id,
                'user_id' => $owner->id,
            ]);
        } finally {
            $owner->delete();
            $otherUser->delete();
        }
    }

    public function test_past_appointment_time_cannot_be_changed_but_status_can_be_managed(): void
    {
        $user = User::create([
            'name' => 'Past Appointment Doctor',
            'username' => Str::lower(Str::random(8)),
            'role' => 2,
            'email' => 'past-appointment-'.Str::uuid().'@example.test',
            'password' => Str::random(32),
        ]);
        $pastDateTime = now()->subDays(2);
        $appointment = $user->appointments()->create([
            'patient_name' => 'Past Visit Patient',
            'phone' => '+911234567890',
            'appointment_at' => $pastDateTime,
            'duration_minutes' => 30,
            'reason' => 'Completed visit',
            'status' => 'scheduled',
        ]);

        try {
            $this->actingAs($user, 'user')
                ->get(route('user.appointments.index'))
                ->assertOk()
                ->assertSee('Past appointment times cannot be changed.')
                ->assertSee('type="hidden" name="appointment_at" value="'.$pastDateTime->format('Y-m-d\TH:i').'"', false);

            $this->from(route('user.appointments.index'))
                ->actingAs($user, 'user')
                ->put(route('user.appointments.update', $appointment), [
                    'patient_name' => 'Past Visit Patient',
                    'phone' => '+911234567890',
                    'appointment_at' => $pastDateTime->format('Y-m-d\TH:i'),
                    'duration_minutes' => 30,
                    'reason' => 'Completed visit',
                    'status' => 'completed',
                ])
                ->assertRedirect(route('user.appointments.index'))
                ->assertSessionHas('status', 'Appointment updated successfully.');

            $this->assertSame('completed', $appointment->fresh()->status);
        } finally {
            $user->delete();
        }
    }

    public function test_hospital_appointments_are_assigned_only_to_its_own_active_doctors(): void
    {
        $hospitalUser = User::create([
            'name' => 'Appointment Hospital',
            'username' => Str::lower(Str::random(8)),
            'role' => 1,
            'email' => 'appointment-hospital-'.Str::uuid().'@example.test',
            'password' => Str::random(32),
        ]);
        $otherHospitalUser = User::create([
            'name' => 'Other Appointment Hospital',
            'username' => Str::lower(Str::random(8)),
            'role' => 1,
            'email' => 'appointment-other-hospital-'.Str::uuid().'@example.test',
            'password' => Str::random(32),
        ]);
        $category = Category::create([
            'name' => 'Appointment specialty '.Str::uuid(),
            'visual_type' => 'icon',
            'visual_value' => 'fas fa-heart',
            'is_active' => true,
        ]);
        $hospital = Hospital::create([
            'user_id' => $hospitalUser->id,
            'name' => 'Appointment Hospital',
            'email' => 'appointment-clinic-'.Str::uuid().'@example.test',
            'phone' => '+911234567890',
        ]);
        $otherHospital = Hospital::create([
            'user_id' => $otherHospitalUser->id,
            'name' => 'Other Appointment Hospital',
            'email' => 'appointment-other-clinic-'.Str::uuid().'@example.test',
            'phone' => '+911234567891',
        ]);
        $doctor = Doctor::create([
            'category_id' => $category->id,
            'hospital_id' => $hospital->id,
            'name' => 'Assigned Doctor',
            'email' => 'assigned-doctor-'.Str::uuid().'@example.test',
            'phone' => '+911234567892',
            'qualification' => 'MBBS',
            'experience_years' => 5,
            'is_active' => true,
        ]);
        $secondDoctor = Doctor::create([
            'category_id' => $category->id,
            'hospital_id' => $hospital->id,
            'name' => 'Second Doctor',
            'email' => 'second-doctor-'.Str::uuid().'@example.test',
            'phone' => '+911234567893',
            'qualification' => 'MBBS',
            'experience_years' => 5,
            'is_active' => true,
        ]);
        $foreignDoctor = Doctor::create([
            'category_id' => $category->id,
            'hospital_id' => $otherHospital->id,
            'name' => 'Foreign Doctor',
            'email' => 'foreign-doctor-'.Str::uuid().'@example.test',
            'phone' => '+911234567894',
            'qualification' => 'MBBS',
            'experience_years' => 5,
            'is_active' => true,
        ]);
        $appointmentAt = now()->addDays(4)->startOfHour();

        try {
            $this->actingAs($hospitalUser, 'user')
                ->post(route('user.appointments.store'), [
                    'doctor_id' => $doctor->id,
                    'patient_name' => 'Hospital Patient',
                    'phone' => '+911234567895',
                    'appointment_at' => $appointmentAt->format('Y-m-d\TH:i'),
                    'duration_minutes' => 30,
                    'reason' => 'Consultation',
                ])
                ->assertRedirect(route('user.appointments.index'));

            $this->assertDatabaseHas('user_appointments', [
                'user_id' => $hospitalUser->id,
                'doctor_id' => $doctor->id,
                'patient_name' => 'Hospital Patient',
            ]);

            $this->from(route('user.appointments.index'))
                ->actingAs($hospitalUser, 'user')
                ->post(route('user.appointments.store'), [
                    'doctor_id' => $foreignDoctor->id,
                    'patient_name' => 'Invalid Doctor Patient',
                    'phone' => '+911234567896',
                    'appointment_at' => $appointmentAt->format('Y-m-d\TH:i'),
                    'duration_minutes' => 30,
                    'reason' => 'Consultation',
                ])
                ->assertRedirect(route('user.appointments.index'))
                ->assertSessionHasErrors('doctor_id');

            $this->actingAs($hospitalUser, 'user')
                ->post(route('user.appointments.store'), [
                    'doctor_id' => $secondDoctor->id,
                    'patient_name' => 'Second Doctor Patient',
                    'phone' => '+911234567897',
                    'appointment_at' => $appointmentAt->format('Y-m-d\TH:i'),
                    'duration_minutes' => 30,
                    'reason' => 'Consultation',
                ])
                ->assertRedirect(route('user.appointments.index'));

            $this->from(route('user.appointments.index'))
                ->actingAs($hospitalUser, 'user')
                ->post(route('user.appointments.store'), [
                    'doctor_id' => $doctor->id,
                    'patient_name' => 'Duplicate Slot Patient',
                    'phone' => '+911234567898',
                    'appointment_at' => $appointmentAt->copy()->addMinutes(10)->format('Y-m-d\TH:i'),
                    'duration_minutes' => 30,
                    'reason' => 'Follow-up',
                ])
                ->assertRedirect(route('user.appointments.index'))
                ->assertSessionHasErrors('appointment_at');
        } finally {
            Doctor::whereIn('id', [$doctor->id, $secondDoctor->id, $foreignDoctor->id])->delete();
            $hospitalUser->delete();
            $otherHospitalUser->delete();
            $category->delete();
        }
    }

    public function test_user_account_cannot_access_admin_registration_inbox(): void
    {
        $user = User::create([
            'name' => 'Regular User',
            'username' => Str::lower(Str::random(8)),
            'role' => 2,
            'email' => 'regular-'.Str::uuid().'@example.test',
            'password' => Str::random(32),
        ]);

        try {
            $this->actingAs($user, 'user')
                ->get(route('admin.registration-requests.index'))
                ->assertRedirect(route('admin.login'));
        } finally {
            $user->delete();
        }
    }

    public function test_homepage_displays_active_database_listings_and_live_counts(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Website launch offer')
            ->assertSee('20% off eligible domain registration')
            ->assertSee('first 6 months')
            ->assertSee('first 3 months')
            ->assertSee('24/7 support for website and hosting assistance');

        $suffix = Str::uuid()->toString();
        $category = Category::create([
            'name' => 'Homepage '.$suffix,
            'visual_type' => 'icon',
            'visual_value' => 'fas fa-user-md',
            'is_active' => true,
        ]);
        $activeDoctor = Doctor::create([
            'category_id' => $category->id,
            'name' => 'Active doctor '.$suffix,
            'email' => 'active-'.$suffix.'@example.test',
            'phone' => '0000000000',
            'qualification' => 'MBBS',
            'is_active' => true,
        ]);
        $inactiveDoctor = Doctor::create([
            'category_id' => $category->id,
            'name' => 'Inactive doctor '.$suffix,
            'email' => 'inactive-'.$suffix.'@example.test',
            'phone' => '0000000000',
            'qualification' => 'MBBS',
            'is_active' => false,
        ]);
        $hospital = Hospital::create([
            'name' => 'Active hospital '.$suffix,
            'email' => 'hospital-'.$suffix.'@example.test',
            'phone' => '0000000000',
            'is_active' => true,
        ]);

        try {
            $doctorCount = Doctor::where('is_active', true)->count();
            $hospitalCount = Hospital::where('is_active', true)->count();

            $this->get('/')
                ->assertOk()
                ->assertSee($activeDoctor->name)
                ->assertSee($hospital->name)
                ->assertDontSee($inactiveDoctor->name)
                ->assertSee('<strong>'.number_format($doctorCount).'</strong>', false)
                ->assertSee('<strong>'.number_format($hospitalCount).'</strong>', false);
        } finally {
            $activeDoctor->delete();
            $inactiveDoctor->delete();
            $hospital->delete();
            $category->delete();
        }
    }

    public function test_admin_and_user_areas_redirect_to_their_own_login_pages(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/user')->assertRedirect('/login');
        $this->get('/user/packages')->assertRedirect('/login');
    }

    public function test_admin_and_user_dashboard_route_names_resolve_to_their_own_paths(): void
    {
        $this->assertSame(url('/admin'), route('admin.index'));
        $this->assertSame(url('/user'), route('user.index'));
        $this->assertSame(url('/user/packages'), route('user.packages.index'));
        $this->assertSame(url('/admin/users/7/api-token'), route('admin.users.api-token', 7));
        $this->assertSame(url('/admin/api-domains'), route('api-domains.index'));
    }

    public function test_user_api_endpoint_requires_an_api_token_or_sanctum_authentication(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_galleries_api_accepts_encrypted_user_api_key_and_returns_active_images(): void
    {
        $userId = null;

        try {
            $user = User::create([
                'name' => 'Gallery API Test',
                'username' => Str::lower(Str::random(8)),
                'role' => 2,
                'email' => Str::uuid().'@example.test',
                'password' => Str::random(32),
            ]);
            $userId = $user->id;
            $token = Str::random(16);
            $user->api_token = $token;
            $user->save();
            $user->apiDomains()->create(['domain' => 'gallery-api.example.test']);
            $activeImage = $user->galleryImages()->create([
                'title' => 'Clinic gallery',
                'caption' => 'Reception area',
                'image_path' => '1/gallery-test.jpg',
                'is_active' => true,
            ]);
            $inactiveImage = $user->galleryImages()->create([
                'title' => 'Inactive image',
                'caption' => null,
                'image_path' => '1/inactive-gallery-test.jpg',
            ]);
            $inactiveImage->is_active = false;
            $inactiveImage->save();

            $this->getJson('/api/galleries', [
                'X-API-KEY' => 'invalid-test-key',
                'Origin' => 'https://gallery-api.example.test',
            ])->assertUnauthorized()
                ->assertJsonPath('message', 'Invalid API key');

            $this->getJson('/api/galleries', [
                'X-API-KEY' => $token,
                'Origin' => 'https://other.example.test',
            ])->assertForbidden();

            $this->getJson('/api/galleries', [
                'X-API-KEY' => $token,
                'Origin' => 'https://gallery-api.example.test',
            ])->assertOk()
                ->assertJsonPath('error', false)
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.id', $activeImage->id)
                ->assertJsonPath('data.0.title', 'Clinic gallery')
                ->assertJsonPath('data.0.image_url', asset('public/1/gallery-test.jpg'))
                ->assertJsonMissingPath('data.0.image_path');
        } finally {
            if ($userId) {
                UserApiDomain::where('user_id', $userId)->delete();
                UserGalleryImage::where('user_id', $userId)->delete();
                User::whereKey($userId)->delete();
            }
        }
    }

    public function test_admin_can_view_an_existing_api_token_without_replacing_it(): void
    {
        $userId = null;

        try {
            $user = User::create([
                'name' => 'API Token Test',
                'username' => Str::lower(Str::random(8)),
                'role' => 2,
                'email' => Str::uuid().'@example.test',
                'password' => Str::random(32),
            ]);
            $userId = $user->id;
            $admin = new Admin([
                'name' => 'API Test Admin',
                'email' => 'api-test-admin@example.test',
                'password' => 'unused',
            ]);
            $admin->id = 1;

            $response = $this->actingAs($admin, 'admin')
                ->postJson(route('admin.users.api-token', $user));
            $response->assertCreated()->assertHeader('Cache-Control', 'no-store, private');
            $firstToken = $response->json('token');
            $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{16}$/', $firstToken);

            UserApiDomain::create([
                'user_id' => $user->id,
                'domain' => 'api-test.example.test',
            ]);

            $rawStoredToken = DB::table('users')->where('id', $user->id)->value('api_token');
            $this->assertNotSame($firstToken, $rawStoredToken);
            $this->assertSame($firstToken, $user->fresh()->api_token);

            $this->withToken($firstToken)
                ->withHeader('Origin', 'https://api-test.example.test')
                ->getJson('/api/user')
                ->assertOk()
                ->assertJsonPath('id', $user->id)
                ->assertJsonMissingPath('api_token');

            $this->actingAs($admin, 'admin')
                ->getJson(route('admin.users.api-token.view', $user))
                ->assertOk()
                ->assertHeader('Cache-Control', 'no-store, private')
                ->assertJsonPath('token', $firstToken)
                ->assertJsonPath('message', 'Existing API token. It has not been changed.');

            $this->postJson(route('admin.users.api-token', $user))
                ->assertConflict();
            $this->assertSame($firstToken, $user->fresh()->api_token);
            $this->withToken($firstToken)
                ->withHeader('Origin', 'https://api-test.example.test')
                ->getJson('/api/user')
                ->assertOk();
        } finally {
            if ($userId) {
                UserApiDomain::where('user_id', $userId)->delete();
                User::whereKey($userId)->delete();
            }
        }
    }

    public function test_admin_can_bind_an_api_token_to_a_domain(): void
    {
        $userId = null;

        try {
            $user = User::create([
                'name' => 'API Domain Test',
                'username' => Str::lower(Str::random(8)),
                'role' => 1,
                'email' => Str::uuid().'@example.test',
                'password' => Str::random(32),
            ]);
            $user->api_token = Str::random(16);
            $user->save();
            $userId = $user->id;
            $admin = new Admin([
                'name' => 'API Domain Test Admin',
                'email' => 'api-domain-test-admin@example.test',
                'password' => 'unused',
            ]);
            $admin->id = 1;
            $token = $user->api_token;

            $this->actingAs($admin, 'admin')
                ->get(route('api-domains.index'))
                ->assertOk()
                ->assertSee('API Domains')
                ->assertSee('API Domain Test');

            $this->actingAs($admin, 'admin')
                ->post(route('api-domains.store'), [
                    'user_id' => $user->id,
                    'domain' => '  Example.COM. ',
                ])
                ->assertRedirect(route('api-domains.index'));

            $this->assertDatabaseHas('user_api_domains', [
                'user_id' => $user->id,
                'domain' => 'example.com',
            ]);

            $this->withToken($token)->getJson('/api/user')->assertForbidden();
            $this->withToken($token)
                ->withHeader('Origin', 'https://other.example.com')
                ->getJson('/api/user')
                ->assertForbidden();
            $this->withToken($token)
                ->withHeader('Origin', 'https://example.com')
                ->getJson('/api/user')
                ->assertOk()
                ->assertJsonPath('id', $user->id);

            $apiDomain = UserApiDomain::where('user_id', $user->id)->firstOrFail();
            $this->actingAs($admin, 'admin')
                ->patch(route('api-domains.status', $apiDomain))
                ->assertRedirect(route('api-domains.index'));
            $this->assertFalse($apiDomain->fresh()->is_active);
            $this->withToken($token)
                ->withHeader('Origin', 'https://example.com')
                ->getJson('/api/user')
                ->assertForbidden();

            $this->actingAs($admin, 'admin')
                ->patch(route('api-domains.status', $apiDomain))
                ->assertRedirect(route('api-domains.index'));
            $this->assertTrue($apiDomain->fresh()->is_active);
            $this->withToken($token)
                ->withHeader('Origin', 'https://example.com')
                ->getJson('/api/user')
                ->assertOk();

            $this->actingAs($admin, 'admin')
                ->delete(route('api-domains.destroy', $apiDomain))
                ->assertRedirect(route('api-domains.index'));

            $this->withToken($token)
                ->withHeader('Origin', 'https://example.com')
                ->getJson('/api/user')
                ->assertForbidden();
        } finally {
            if ($userId) {
                UserApiDomain::where('user_id', $userId)->delete();
                User::whereKey($userId)->delete();
            }
        }
    }

    public function test_care_partner_dashboard_uses_its_own_panel_layout(): void
    {
        $user = new User([
            'name' => 'Dr. Example',
            'email' => 'doctor@example.test',
            'role' => 2,
        ]);

        $html = view('users.dashboard', ['user' => $user, 'profile' => null])->render();

        $this->assertStringContainsString('class="app-shell"', $html);
        $this->assertStringContainsString('Care partner navigation', $html);
        $this->assertStringContainsString('Dr. Example', $html);
        $this->assertStringContainsString('Sign out', $html);
        $this->assertStringContainsString('Packages', $html);
        $this->assertStringContainsString('href="'.route('user.packages.index').'"', $html);
        $this->assertStringNotContainsString('Masters', $html);
        $this->assertStringNotContainsString('>Doctors</span>', $html);
    }

    public function test_hospital_dashboard_shows_doctors_menu(): void
    {
        $user = new User([
            'name' => 'Example Hospital',
            'email' => 'hospital@example.test',
            'role' => 1,
        ]);

        $html = view('users.dashboard', ['user' => $user, 'profile' => null])->render();

        $this->assertStringContainsString('>Doctors</span>', $html);
        $this->assertStringContainsString('user/doctors', $html);
    }

    public function test_hospital_doctors_page_lists_only_hospital_doctor_information(): void
    {
        $user = new User([
            'name' => 'Example Hospital',
            'email' => 'hospital@example.test',
            'role' => 1,
        ]);
        $hospital = new Hospital([
            'name' => 'Example Hospital',
            'email' => 'hospital@example.test',
            'phone' => '+91 98765 43210',
        ]);
        $hospital->id = 11;
        $doctor = new Doctor([
            'name' => 'Dr. Example',
            'email' => 'doctor@example.test',
            'phone' => '+91 98765 43211',
            'qualification' => 'MBBS, MD',
            'experience_years' => 8,
            'bio' => 'Providing compassionate cardiac care.',
            'is_active' => true,
        ]);
        $doctor->id = 91;
        $doctor->created_at = Carbon::parse('2026-10-26');
        $doctor->is_active = true;

        $html = view('users.doctors.index', [
            'user' => $user,
            'hospital' => $hospital,
            'doctors' => new LengthAwarePaginator(collect([$doctor]), 1, 12),
            'categories' => collect(),
            'filters' => ['q' => '', 'status' => 'all'],
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertSame(url('/user/doctors'), route('user.doctors.index'));
        $this->assertStringContainsString('>Doctors</span>', $html);
        $this->assertStringContainsString('Example Hospital', $html);
        $this->assertStringContainsString('Dr. Example', $html);
        $this->assertStringContainsString('MBBS, MD', $html);
        $this->assertStringContainsString('8 years experience', $html);
        $this->assertStringContainsString('name="q"', $html);
        $this->assertStringContainsString('name="status"', $html);
        $this->assertStringContainsString('Add doctor', $html);
        $this->assertStringContainsString('name="category_id"', $html);
        $this->assertStringContainsString('name="experience_years"', $html);
        $this->assertStringContainsString('action="'.route('user.doctors.store').'"', $html);
    }

    public function test_doctor_accounts_cannot_add_hospital_doctors(): void
    {
        $doctorUser = new User([
            'name' => 'Dr. Example',
            'email' => 'doctor@example.test',
            'role' => 2,
        ]);

        $this->actingAs($doctorUser, 'user')
            ->post('/user/doctors', [])
            ->assertForbidden();
    }

    public function test_care_partner_reviews_page_renders_review_form_and_saved_reviews_area(): void
    {
        $user = new User([
            'name' => 'Dr. Example',
            'email' => 'doctor@example.test',
            'role' => 2,
        ]);

        $html = view('users.reviews.index', [
            'user' => $user,
            'reviews' => new LengthAwarePaginator(collect(), 0, 10),
            'filters' => ['q' => '', 'status' => 'all'],
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertStringContainsString('Add a review', $html);
        $this->assertStringContainsString('name="reviewer_name"', $html);
        $this->assertStringContainsString('name="rating"', $html);
        $this->assertStringContainsString('name="review_text"', $html);
        $this->assertStringContainsString('Saved reviews', $html);
        $this->assertStringContainsString('user/reviews', $html);
        $this->assertStringNotContainsString('Review source', $html);
        $this->assertStringNotContainsString('name="source"', $html);
        $this->assertStringContainsString('value="5"', $html);
        $this->assertStringNotContainsString('value="6"', $html);
        $this->assertSame(5, substr_count($html, '★'));
        $this->assertStringContainsString('name="q"', $html);
        $this->assertStringContainsString('name="status"', $html);
        $this->assertStringContainsString('review-list-scroll', $html);
    }

    public function test_saved_reviews_show_edit_and_active_status_controls(): void
    {
        $user = new User([
            'name' => 'Dr. Example',
            'email' => 'doctor@example.test',
            'role' => 2,
        ]);
        $review = new UserReview([
            'reviewer_name' => 'Patient Example',
            'rating' => 4,
            'review_text' => 'Excellent care',
            'is_active' => true,
        ]);
        $review->id = 7;
        $review->created_at = Carbon::parse('2026-10-26');
        $review->is_active = true;

        $html = view('users.reviews.index', [
            'user' => $user,
            'reviews' => new LengthAwarePaginator(collect([$review]), 1, 10),
            'filters' => ['q' => '', 'status' => 'all'],
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertStringContainsString('Patient Example', $html);
        $this->assertStringContainsString('Edit', $html);
        $this->assertStringContainsString('Save changes', $html);
        $this->assertStringContainsString('Active', $html);
        $this->assertStringContainsString('Deactivate', $html);
        $this->assertStringContainsString('/user/reviews/7', $html);
        $this->assertStringContainsString('PATCH', $html);
        $this->assertStringContainsString('data-open-review-edit="review-edit-dialog-7"', $html);
        $this->assertStringContainsString('data-close-review-edit', $html);
        $this->assertStringContainsString('Close edit review', $html);
    }

    public function test_review_qr_page_has_two_printable_designs_and_scannable_url(): void
    {
        $user = new User([
            'name' => 'Dr. Example',
            'email' => 'doctor@example.test',
            'role' => 2,
        ]);
        $reviewUrl = route('public.reviews.create', str_repeat('a', 64));
        $qrCode = preg_replace('/^<\?xml[^>]*>\s*/', '', (new Writer(new ImageRenderer(
            new RendererStyle(320, 4),
            new SvgImageBackEnd()
        )))->writeString($reviewUrl));

        $html = view('users.reviews.qr', compact('user', 'reviewUrl', 'qrCode'))->render();

        $this->assertStringContainsString('Design 1', $html);
        $this->assertStringContainsString('Design 2', $html);
        $this->assertStringContainsString('id="qr-design-light"', $html);
        $this->assertStringContainsString('id="qr-design-green"', $html);
        $this->assertSame(2, substr_count($html, '<svg'));
        $this->assertStringContainsString($reviewUrl, $html);
        $this->assertSame(url('/review/'.str_repeat('a', 64)), $reviewUrl);
        $this->assertStringContainsString('Print', $html);
    }

    public function test_public_review_page_has_five_star_submission_form(): void
    {
        $user = new User([
            'name' => 'Dr. Example',
            'email' => 'doctor@example.test',
            'role' => 2,
        ]);
        $token = str_repeat('a', 64);

        $html = view('public-reviews.create', [
            'user' => $user,
            'token' => $token,
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertStringContainsString('action="'.route('public.reviews.store', $token).'"', $html);
        $this->assertStringContainsString('name="reviewer_name"', $html);
        $this->assertStringContainsString('name="rating"', $html);
        $this->assertStringContainsString('name="review_text"', $html);
        $this->assertSame(5, substr_count($html, '★'));
    }

    public function test_faq_menu_and_page_use_the_care_partner_panel(): void
    {
        $user = new User([
            'name' => 'Dr. Example',
            'email' => 'doctor@example.test',
            'role' => 2,
        ]);
        $faq = new UserFaq([
            'question' => 'What are the clinic hours?',
            'answer' => 'We are open from 9 AM to 5 PM.',
            'is_active' => true,
        ]);
        $faq->id = 12;
        $faq->created_at = Carbon::parse('2026-10-26');
        $faq->is_active = true;

        $html = view('users.faqs.index', [
            'user' => $user,
            'faqs' => new LengthAwarePaginator(collect([$faq]), 1, 10),
            'filters' => ['q' => '', 'status' => 'all'],
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertSame(url('/user/faqs'), route('user.faqs.index'));
        $this->assertStringContainsString('class="app-shell"', $html);
        $this->assertStringContainsString('>FAQs</span>', $html);
        $this->assertStringContainsString('Add a FAQ', $html);
        $this->assertStringContainsString('name="question"', $html);
        $this->assertStringContainsString('What are the clinic hours?', $html);
        $this->assertStringContainsString('We are open from 9 AM to 5 PM.', $html);
        $this->assertStringContainsString('Saved FAQs', $html);
        $this->assertStringContainsString('data-open-faq-edit="faq-edit-dialog-12"', $html);
        $this->assertStringContainsString('Close edit FAQ', $html);
        $this->assertStringContainsString('Deactivate', $html);
        $this->assertStringContainsString('name="q"', $html);
        $this->assertStringContainsString('name="status"', $html);
    }

    public function test_gallery_menu_and_page_render_upload_and_image_management_controls(): void
    {
        $user = new User([
            'name' => 'Dr. Example',
            'email' => 'doctor@example.test',
            'role' => 2,
        ]);
        $image = new UserGalleryImage([
            'title' => 'Clinic reception',
            'caption' => 'Welcome to our clinic.',
            'image_path' => '5/reception.jpg',
            'is_active' => true,
        ]);
        $image->id = 19;
        $image->created_at = Carbon::parse('2026-10-26');
        $image->is_active = true;

        $html = view('users.gallery.index', [
            'user' => $user,
            'images' => new LengthAwarePaginator(collect([$image]), 1, 12),
            'filters' => ['q' => '', 'status' => 'all'],
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertSame(url('/user/gallery'), route('user.gallery.index'));
        $this->assertStringContainsString('>Gallery</span>', $html);
        $this->assertStringContainsString('enctype="multipart/form-data"', $html);
        $this->assertStringContainsString('name="image"', $html);
        $this->assertStringContainsString('Clinic reception', $html);
        $this->assertStringContainsString('data-open-gallery-edit="gallery-edit-dialog-19"', $html);
        $this->assertStringContainsString('Close edit gallery image', $html);
        $this->assertStringContainsString('Deactivate', $html);
        $this->assertStringContainsString('/user/gallery/19', $html);
        $this->assertStringContainsString('Delete this gallery image permanently?', $html);
        $this->assertStringContainsString('DELETE', $html);
        $this->assertStringContainsString('name="q"', $html);
        $this->assertStringContainsString('name="status"', $html);
        $this->assertStringContainsString('src="'.asset('5/reception.jpg').'"', $html);
    }

    public function test_video_menu_page_supports_single_upload_or_youtube_source(): void
    {
        $user = new User([
            'name' => 'Dr. Example',
            'email' => 'doctor@example.test',
            'role' => 2,
        ]);
        $video = new UserVideo([
            'title' => 'Meet our care team',
            'caption' => 'A short introduction.',
            'video_type' => 'youtube',
            'youtube_video_id' => 'abcdefghijk',
            'is_active' => true,
        ]);
        $video->id = 23;
        $video->created_at = Carbon::parse('2026-10-26');
        $video->is_active = true;
        $uploadedVideo = new UserVideo([
            'title' => 'Patient welcome',
            'caption' => 'A locally uploaded video.',
            'video_type' => 'upload',
            'video_path' => '1/patient-welcome.mp4',
            'is_active' => true,
        ]);
        $uploadedVideo->id = 24;
        $uploadedVideo->created_at = Carbon::parse('2026-10-26');
        $uploadedVideo->is_active = true;

        $html = view('users.videos.index', [
            'user' => $user,
            'videos' => new LengthAwarePaginator(collect([$video, $uploadedVideo]), 2, 10),
            'filters' => ['q' => '', 'status' => 'all'],
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertSame(url('/user/videos'), route('user.videos.index'));
        $this->assertStringContainsString('>Videos</span>', $html);
        $this->assertStringContainsString('name="video_type" value="upload"', $html);
        $this->assertStringContainsString('name="video_type" value="youtube"', $html);
        $this->assertStringContainsString('name="video"', $html);
        $this->assertStringContainsString('name="youtube_url"', $html);
        $this->assertStringContainsString('https://www.youtube-nocookie.com/embed/abcdefghijk', $html);
        $this->assertStringContainsString('src="'.route('user.videos.stream', 24).'"', $html);
        $this->assertStringContainsString('data-open-video-edit="video-edit-dialog-23"', $html);
        $this->assertStringContainsString('Close edit video', $html);
        $this->assertStringContainsString('Deactivate', $html);
        $this->assertStringContainsString('Delete this video permanently?', $html);
        $this->assertStringContainsString('name="q"', $html);
        $this->assertStringContainsString('name="status"', $html);
    }

    public function test_news_menu_page_uses_an_image_and_supports_article_management(): void
    {
        $user = new User([
            'name' => 'Dr. Example',
            'email' => 'doctor@example.test',
            'role' => 2,
        ]);
        $news = new UserNews([
            'title' => 'New clinic hours',
            'content' => 'We are now open on Saturday mornings.',
            'image_path' => '5/clinic-news.jpg',
            'is_active' => true,
        ]);
        $news->id = 31;
        $news->created_at = Carbon::parse('2026-10-26');
        $news->is_active = true;

        $html = view('users.news.index', [
            'user' => $user,
            'newsItems' => new LengthAwarePaginator(collect([$news]), 1, 10),
            'filters' => ['q' => '', 'status' => 'all'],
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertSame(url('/user/news'), route('user.news.index'));
        $this->assertStringContainsString('>News</span>', $html);
        $this->assertStringContainsString('name="title"', $html);
        $this->assertStringContainsString('name="content"', $html);
        $this->assertStringContainsString('name="image"', $html);
        $this->assertStringNotContainsString('name="video"', $html);
        $this->assertStringContainsString('New clinic hours', $html);
        $this->assertStringContainsString('We are now open on Saturday mornings.', $html);
        $this->assertStringContainsString('data-open-news-edit="news-edit-dialog-31"', $html);
        $this->assertStringContainsString('Close edit news', $html);
        $this->assertStringContainsString('Deactivate', $html);
        $this->assertStringContainsString('Delete this news article permanently?', $html);
        $this->assertStringContainsString('name="q"', $html);
        $this->assertStringContainsString('name="status"', $html);
        $this->assertStringContainsString('src="'.asset('5/clinic-news.jpg').'"', $html);
    }

    public function test_articles_menu_page_supports_full_articles_with_featured_images(): void
    {
        $user = new User([
            'name' => 'Dr. Example',
            'email' => 'doctor@example.test',
            'role' => 2,
        ]);
        $article = new UserArticle([
            'title' => 'Preparing for your first visit',
            'content' => 'Bring your medical history and current medications.',
            'image_path' => '5/first-visit.jpg',
            'is_active' => true,
        ]);
        $article->id = 32;
        $article->created_at = Carbon::parse('2026-10-26');
        $article->is_active = true;

        $html = view('users.articles.index', [
            'user' => $user,
            'articles' => new LengthAwarePaginator(collect([$article]), 1, 10),
            'filters' => ['q' => '', 'status' => 'all'],
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertSame(url('/user/articles'), route('user.articles.index'));
        $this->assertStringContainsString('>Articles</span>', $html);
        $this->assertStringContainsString('name="title"', $html);
        $this->assertStringContainsString('name="content"', $html);
        $this->assertStringContainsString('name="image"', $html);
        $this->assertStringNotContainsString('name="video"', $html);
        $this->assertStringContainsString('Preparing for your first visit', $html);
        $this->assertStringContainsString('data-open-article-edit="article-edit-dialog-32"', $html);
        $this->assertStringContainsString('Close edit article', $html);
        $this->assertStringContainsString('Deactivate', $html);
        $this->assertStringContainsString('Delete this article permanently?', $html);
        $this->assertStringContainsString('name="q"', $html);
        $this->assertStringContainsString('name="status"', $html);
        $this->assertStringContainsString('src="'.asset('5/first-visit.jpg').'"', $html);
    }

    public function test_services_menu_separates_catalog_customizations_from_user_services(): void
    {
        $user = new User([
            'name' => 'Dr. Example',
            'email' => 'doctor@example.test',
            'role' => 2,
        ]);
        $category = new Category(['name' => 'Cardiology']);
        $category->id = 4;
        $service = new Service([
            'category_id' => 4,
            'name' => 'Heart consultation',
            'visual_type' => 'icon',
            'visual_value' => 'fas fa-heartbeat',
            'is_active' => true,
        ]);
        $service->id = 14;
        $override = new UserService([
            'service_id' => 14,
            'title' => 'Personalized heart consultation',
            'content' => 'Appointments available throughout the week.',
            'is_active' => false,
        ]);
        $override->id = 41;
        $customService = new UserService([
            'title' => 'Home care visits',
            'content' => 'Care delivered at home.',
            'image_path' => '5/home-care.jpg',
            'is_active' => true,
        ]);
        $customService->id = 42;

        $html = view('users.services.index', [
            'user' => $user,
            'categories' => collect([$category]),
            'services' => collect([$service]),
            'overrides' => collect([$override])->keyBy('service_id'),
            'customServices' => collect([$customService]),
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertSame(url('/user/services'), route('user.services.index'));
        $this->assertStringContainsString('>Services</span>', $html);
        $this->assertStringContainsString('Cardiology', $html);
        $this->assertStringContainsString('Personalized heart consultation', $html);
        $this->assertStringContainsString('Appointments available throughout the week.', $html);
        $this->assertStringContainsString('Home care visits', $html);
        $this->assertStringContainsString('name="title"', $html);
        $this->assertStringContainsString('name="content"', $html);
        $this->assertStringContainsString('name="image"', $html);
        $this->assertStringContainsString('Deactivate', $html);
        $this->assertStringContainsString('Activate', $html);
        $this->assertStringContainsString('/user/services/catalog/14', $html);
        $this->assertStringContainsString('/user/services/custom/42', $html);
        $this->assertStringContainsString('src="'.asset('5/home-care.jpg').'"', $html);
        $this->assertStringContainsString('Your changes are stored separately from the admin service.', $html);
    }

    public function test_blogs_menu_supports_image_based_blog_post_management(): void
    {
        $user = new User([
            'name' => 'Dr. Example',
            'email' => 'doctor@example.test',
            'role' => 2,
        ]);
        $blog = new UserBlog([
            'title' => 'Everyday heart health',
            'content' => 'Small daily habits can support a healthier heart.',
            'image_path' => '5/heart-health.jpg',
            'is_active' => true,
        ]);
        $blog->id = 52;
        $blog->created_at = Carbon::parse('2026-10-26');
        $blog->is_active = true;

        $html = view('users.blogs.index', [
            'user' => $user,
            'blogs' => new LengthAwarePaginator(collect([$blog]), 1, 10),
            'filters' => ['q' => '', 'status' => 'all'],
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertSame(url('/user/blogs'), route('user.blogs.index'));
        $this->assertStringContainsString('>Blogs</span>', $html);
        $this->assertStringContainsString('name="title"', $html);
        $this->assertStringContainsString('name="content"', $html);
        $this->assertStringContainsString('name="image"', $html);
        $this->assertStringNotContainsString('name="video"', $html);
        $this->assertStringContainsString('Everyday heart health', $html);
        $this->assertStringContainsString('data-open-blog-edit="blog-edit-dialog-52"', $html);
        $this->assertStringContainsString('Close edit blog', $html);
        $this->assertStringContainsString('Deactivate', $html);
        $this->assertStringContainsString('Delete this blog post permanently?', $html);
        $this->assertStringContainsString('name="q"', $html);
        $this->assertStringContainsString('name="status"', $html);
        $this->assertStringContainsString('src="'.asset('5/heart-health.jpg').'"', $html);
    }

    public function test_slider_menu_supports_ordered_image_slide_management(): void
    {
        $user = new User([
            'name' => 'Dr. Example',
            'email' => 'doctor@example.test',
            'role' => 2,
        ]);
        $slide = new UserSliderItem([
            'title' => 'Compassionate care',
            'caption' => 'Here when you need us.',
            'image_path' => '5/slider-banner.jpg',
            'sort_order' => 2,
            'is_active' => true,
        ]);
        $slide->id = 62;
        $slide->created_at = Carbon::parse('2026-10-26');
        $slide->is_active = true;

        $html = view('users.slider.index', [
            'user' => $user,
            'slides' => new LengthAwarePaginator(collect([$slide]), 1, 12),
            'filters' => ['q' => '', 'status' => 'all'],
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertSame(url('/user/slider'), route('user.slider.index'));
        $this->assertStringContainsString('>Slider</span>', $html);
        $this->assertStringContainsString('name="title"', $html);
        $this->assertStringContainsString('name="caption"', $html);
        $this->assertStringContainsString('name="sort_order"', $html);
        $this->assertStringContainsString('name="image"', $html);
        $this->assertStringContainsString('Compassionate care', $html);
        $this->assertStringContainsString('Order 2', $html);
        $this->assertStringContainsString('data-open-slider-edit="slider-edit-dialog-62"', $html);
        $this->assertStringContainsString('Close edit slide', $html);
        $this->assertStringContainsString('Deactivate', $html);
        $this->assertStringContainsString('Delete this slider slide permanently?', $html);
        $this->assertStringContainsString('name="q"', $html);
        $this->assertStringContainsString('name="status"', $html);
        $this->assertStringContainsString('src="'.asset('5/slider-banner.jpg').'"', $html);
    }

    public function test_cms_pages_menu_supports_page_title_slug_content_and_image(): void
    {
        $user = new User([
            'name' => 'Dr. Example',
            'email' => 'doctor@example.test',
            'role' => 2,
        ]);
        $page = new UserCmsPage([
            'title' => 'About our clinic',
            'slug' => 'about-our-clinic',
            'content' => 'Our clinic provides thoughtful care for every patient.',
            'image_path' => '5/about-clinic.jpg',
            'is_active' => true,
        ]);
        $page->id = 72;
        $page->created_at = Carbon::parse('2026-10-26');
        $page->is_active = true;

        $html = view('users.cms-pages.index', [
            'user' => $user,
            'pages' => new LengthAwarePaginator(collect([$page]), 1, 10),
            'filters' => ['q' => '', 'status' => 'all'],
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertSame(url('/user/cms-pages'), route('user.cms-pages.index'));
        $this->assertStringContainsString('>CMS Pages</span>', $html);
        $this->assertStringContainsString('name="title"', $html);
        $this->assertStringContainsString('name="slug"', $html);
        $this->assertStringContainsString('name="content"', $html);
        $this->assertStringContainsString('name="image"', $html);
        $this->assertStringContainsString('About our clinic', $html);
        $this->assertStringContainsString('/about-our-clinic', $html);
        $this->assertStringContainsString('data-open-cms-page-edit="cms-page-edit-dialog-72"', $html);
        $this->assertStringContainsString('Close edit page', $html);
        $this->assertStringContainsString('Deactivate', $html);
        $this->assertStringContainsString('Delete this CMS page permanently?', $html);
        $this->assertStringContainsString('name="q"', $html);
        $this->assertStringContainsString('name="status"', $html);
        $this->assertStringContainsString('src="'.asset('5/about-clinic.jpg').'"', $html);
    }

    public function test_about_us_editor_supports_optional_hero_and_custom_sections(): void
    {
        $user = new User([
            'name' => 'Example Medical Center',
            'email' => 'hospital@example.test',
            'role' => 1,
        ]);
        $aboutPage = new UserAboutUsPage([
            'hero_title' => 'Care for every chapter',
            'intro' => 'A welcoming place for the whole family.',
            'hero_image_path' => '5/about-hero.jpg',
            'sections' => [
                [
                    'key' => 'our-story',
                    'title' => 'Our Story',
                    'content' => 'We have cared for our community for many years.',
                    'image_path' => '5/our-story.jpg',
                ],
                [
                    'key' => 'our-values',
                    'title' => 'Our Values',
                    'content' => 'Compassion, trust and respect.',
                    'image_path' => null,
                ],
            ],
        ]);

        $html = view('users.about-us.index', [
            'user' => $user,
            'aboutPage' => $aboutPage,
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertSame(url('/user/about-us'), route('user.about-us.index'));
        $this->assertStringContainsString('>About Us</span>', $html);
        $this->assertStringContainsString('name="hero_title"', $html);
        $this->assertStringContainsString('name="intro"', $html);
        $this->assertStringContainsString('name="hero_image"', $html);
        $this->assertStringContainsString('Our Story', $html);
        $this->assertStringContainsString('Our Values', $html);
        $this->assertStringContainsString('name="sections[0][title]"', $html);
        $this->assertStringContainsString('name="sections[1][content]"', $html);
        $this->assertStringContainsString('data-add-about-section', $html);
        $this->assertStringContainsString('data-remove-about-section', $html);
        $this->assertStringContainsString('data-move-about-section="-1"', $html);
        $this->assertStringContainsString('data-move-about-section="1"', $html);
        $this->assertStringContainsString('src="'.asset('5/about-hero.jpg').'"', $html);
        $this->assertStringContainsString('src="'.asset('5/our-story.jpg').'"', $html);
    }

    public function test_smtp_menu_uses_private_settings_and_masks_saved_password(): void
    {
        $user = new User([
            'name' => 'Dr. Example',
            'email' => 'doctor@example.test',
            'role' => 2,
        ]);
        $settings = new UserSmtpSetting([
            'host' => 'smtp.example.test',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'mailer@example.test',
            'password' => 'top-secret-smtp-password',
            'from_email' => 'care@example.test',
            'from_name' => 'Example Care',
        ]);

        $html = view('users.smtp.index', [
            'user' => $user,
            'settings' => $settings,
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertSame(url('/user/smtp'), route('user.smtp.index'));
        $this->assertStringContainsString('>SMTP Setup</span>', $html);
        $this->assertStringContainsString('name="host"', $html);
        $this->assertStringContainsString('name="port"', $html);
        $this->assertStringContainsString('name="encryption"', $html);
        $this->assertStringContainsString('name="username"', $html);
        $this->assertStringContainsString('name="password"', $html);
        $this->assertStringContainsString('name="from_email"', $html);
        $this->assertStringContainsString('name="from_name"', $html);
        $this->assertStringContainsString('name="recipient"', $html);
        $this->assertStringContainsString('smtp.example.test', $html);
        $this->assertStringNotContainsString('top-secret-smtp-password', $html);
        $this->assertNotSame('top-secret-smtp-password', $settings->getAttributes()['password']);
        $this->assertSame('top-secret-smtp-password', $settings->password);
        $this->assertStringContainsString('password is never shown after saving', $html);
        $this->assertStringContainsString('name="_method" value="PUT"', $html);
    }

    public function test_social_media_menu_supports_profile_link_management(): void
    {
        $user = new User([
            'name' => 'Dr. Example',
            'email' => 'doctor@example.test',
            'role' => 2,
        ]);
        $link = new UserSocialMediaLink([
            'platform' => 'Instagram',
            'url' => 'https://instagram.com/example.care',
            'is_active' => true,
        ]);
        $link->id = 82;
        $link->created_at = Carbon::parse('2026-10-26');
        $link->is_active = true;

        $html = view('users.social-media.index', [
            'user' => $user,
            'socialLinks' => new LengthAwarePaginator(collect([$link]), 1, 12),
            'filters' => ['q' => '', 'status' => 'all'],
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertSame(url('/user/social-media'), route('user.social-media.index'));
        $this->assertStringContainsString('>Social Media</span>', $html);
        $this->assertStringContainsString('name="platform"', $html);
        $this->assertStringContainsString('name="url"', $html);
        $this->assertStringContainsString('Instagram', $html);
        $this->assertStringContainsString('https://instagram.com/example.care', $html);
        $this->assertStringContainsString('rel="noopener noreferrer"', $html);
        $this->assertStringContainsString('data-open-social-edit="social-edit-dialog-82"', $html);
        $this->assertStringContainsString('Close edit social profile', $html);
        $this->assertStringContainsString('Deactivate', $html);
        $this->assertStringContainsString('Delete this social media link permanently?', $html);
        $this->assertStringContainsString('name="q"', $html);
        $this->assertStringContainsString('name="status"', $html);
    }

    public function test_website_settings_menu_supports_branding_and_contact_details(): void
    {
        $user = new User([
            'name' => 'Dr. Example',
            'email' => 'doctor@example.test',
            'role' => 2,
        ]);
        $settings = new UserWebsiteSetting([
            'site_name' => 'Example Care Clinic',
            'tagline' => 'Care for every chapter',
            'contact_email' => 'contact@example.test',
            'phone' => '+91 98765 43210',
            'whatsapp' => '+91 98765 43211',
            'website_url' => 'https://example.test',
            'address' => '123 Health Street, New Delhi',
            'header_logo_path' => '5/header-logo.png',
            'footer_logo_path' => '5/footer-logo.png',
            'footer_text' => 'Here for your family, every step of the way.',
            'copyright_text' => '© 2026 Example Care Clinic.',
        ]);

        $html = view('users.website-settings.index', [
            'user' => $user,
            'settings' => $settings,
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertSame(url('/user/website-settings'), route('user.website-settings.index'));
        $this->assertStringContainsString('>Website Settings</span>', $html);
        $this->assertStringContainsString('name="site_name"', $html);
        $this->assertStringContainsString('name="tagline"', $html);
        $this->assertStringContainsString('name="header_logo"', $html);
        $this->assertStringContainsString('name="footer_logo"', $html);
        $this->assertStringContainsString('name="contact_email"', $html);
        $this->assertStringContainsString('name="phone"', $html);
        $this->assertStringContainsString('name="whatsapp"', $html);
        $this->assertStringContainsString('name="website_url"', $html);
        $this->assertStringContainsString('name="address"', $html);
        $this->assertStringContainsString('name="footer_text"', $html);
        $this->assertStringContainsString('name="copyright_text"', $html);
        $this->assertStringContainsString('Example Care Clinic', $html);
        $this->assertStringContainsString('src="'.asset('5/header-logo.png').'"', $html);
        $this->assertStringContainsString('src="'.asset('5/footer-logo.png').'"', $html);
    }
}
