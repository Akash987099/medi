<?php

namespace Tests\Feature;

use App\Models\User;
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

    public function test_admin_and_user_areas_redirect_to_their_own_login_pages(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/user')->assertRedirect('/login');
    }

    public function test_admin_and_user_dashboard_route_names_resolve_to_their_own_paths(): void
    {
        $this->assertSame(url('/admin'), route('admin.index'));
        $this->assertSame(url('/user'), route('user.index'));
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
