<script>
  (() => {
    const shell = document.querySelector('.app-shell');
    const menuButton = document.querySelector('.mobile-menu');
    const backdrop = document.querySelector('.sidebar-backdrop');

    if (shell && menuButton && backdrop) {
      const setMenuOpen = (isOpen) => {
        shell.classList.toggle('sidebar-open', isOpen);
        menuButton.setAttribute('aria-expanded', String(isOpen));
        menuButton.setAttribute('aria-label', isOpen ? 'Close navigation' : 'Open navigation');
      };

      menuButton.addEventListener('click', () => {
        setMenuOpen(!shell.classList.contains('sidebar-open'));
      });
      backdrop.addEventListener('click', () => setMenuOpen(false));
      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setMenuOpen(false);
      });
    }

    document.querySelectorAll('.sidebar-toggle').forEach((toggleButton) => {
      const submenu = document.getElementById(toggleButton.getAttribute('aria-controls'));
      if (!submenu) return;

      toggleButton.addEventListener('click', () => {
        const isExpanded = toggleButton.getAttribute('aria-expanded') === 'true';
        toggleButton.setAttribute('aria-expanded', String(!isExpanded));
        submenu.hidden = isExpanded;
      });
    });

    const categoryModal = document.querySelector('#category-modal');
    const categoryOpenButton = document.querySelector('[data-open-category-modal]');
    const categoryEditButtons = document.querySelectorAll('[data-edit-category]');
    const categoryCloseButtons = document.querySelectorAll('[data-close-category-modal]');
    const categoryForm = document.querySelector('#category-form');
    const visualTypeInputs = document.querySelectorAll('input[name="visual_type"]');
    const visualPanels = document.querySelectorAll('[data-visual-panel]');
    const categoryModalTitle = document.querySelector('#category-modal-title');
    const imageHelp = document.querySelector('[data-image-help]');
    let previousFocusedElement;

    const syncVisualPanels = () => {
      const selectedType = document.querySelector('input[name="visual_type"]:checked')?.value;
      const isEditing = categoryForm?.dataset.editMode === 'true';
      const currentVisualType = categoryForm?.dataset.currentVisualType;

      visualPanels.forEach((panel) => {
        const isSelected = panel.dataset.visualPanel === selectedType;
        panel.hidden = !isSelected;
        panel.querySelectorAll('input, textarea').forEach((input) => {
          input.disabled = !isSelected;
          input.required = isSelected && (
            input.name !== 'image' || !isEditing || currentVisualType !== 'image'
          );
        });
      });
    };

    const closeCategoryModal = () => {
      if (!categoryModal) return;
      categoryModal.hidden = true;
      previousFocusedElement?.focus();
    };

    const openCategoryModal = () => {
      if (!categoryModal) return;
      previousFocusedElement = document.activeElement;
      categoryModal.hidden = false;
      categoryModal.querySelector('#category-name')?.focus();
    };

    const setCategoryMode = (isEditing, categoryId = '', visualType = 'icon') => {
      if (!categoryForm) return;

      const methodInput = categoryForm.querySelector('input[name="_method"]');
      const categoryIdInput = categoryForm.querySelector('input[name="category_id"]');
      categoryForm.dataset.editMode = String(isEditing);
      categoryForm.dataset.currentVisualType = visualType;
      categoryForm.action = isEditing
        ? `${categoryForm.dataset.updateBase}/${encodeURIComponent(categoryId)}`
        : categoryForm.dataset.createAction;

      if (methodInput) {
        methodInput.disabled = !isEditing;
        methodInput.value = 'PUT';
      }
      if (categoryIdInput) categoryIdInput.value = isEditing ? categoryId : '';
      if (categoryModalTitle) categoryModalTitle.textContent = isEditing ? 'Edit category' : 'Add a category';
      if (imageHelp) {
        imageHelp.textContent = isEditing && visualType === 'image'
          ? 'Current image will be kept unless you upload a replacement. JPG, PNG, or WebP; maximum 2 MB.'
          : 'JPG, PNG, or WebP; maximum file size 2 MB.';
      }
      syncVisualPanels();
    };

    if (categoryModal && categoryForm) {
      syncVisualPanels();

      if (categoryForm.dataset.editMode === 'true') {
        const categoryId = categoryForm.querySelector('input[name="category_id"]')?.value;
        const selectedType = document.querySelector('input[name="visual_type"]:checked')?.value || 'icon';
        setCategoryMode(true, categoryId, selectedType);
      }

      categoryOpenButton?.addEventListener('click', () => {
        categoryForm.reset();
        const iconRadio = categoryForm.querySelector('input[name="visual_type"][value="icon"]');
        if (iconRadio) iconRadio.checked = true;
        const iconInput = categoryForm.querySelector('input[name="icon_class"]');
        if (iconInput) iconInput.value = 'fas fa-heart';
        const svgInput = categoryForm.querySelector('textarea[name="svg_code"]');
        if (svgInput) svgInput.value = '';
        setCategoryMode(false);
        openCategoryModal();
      });

      categoryEditButtons.forEach((button) => {
        button.addEventListener('click', () => {
          categoryForm.reset();
          const { categoryId, categoryName, visualType, visualValue } = button.dataset;
          const nameInput = categoryForm.querySelector('input[name="name"]');
          const visualTypeInput = categoryForm.querySelector(`input[name="visual_type"][value="${visualType}"]`);
          if (nameInput) nameInput.value = categoryName || '';
          if (visualTypeInput) visualTypeInput.checked = true;

          if (visualType === 'svg') {
            const svgInput = categoryForm.querySelector('textarea[name="svg_code"]');
            if (svgInput) svgInput.value = visualValue || '';
          } else if (visualType === 'icon') {
            const iconInput = categoryForm.querySelector('input[name="icon_class"]');
            if (iconInput) iconInput.value = visualValue || '';
          }

          setCategoryMode(true, categoryId, visualType);
          openCategoryModal();
        });
      });

      categoryCloseButtons.forEach((button) => {
        button.addEventListener('click', closeCategoryModal);
      });

      categoryModal.addEventListener('click', (event) => {
        if (event.target === categoryModal) closeCategoryModal();
      });

      visualTypeInputs.forEach((input) => {
        input.addEventListener('change', syncVisualPanels);
      });

      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !categoryModal.hidden) closeCategoryModal();
      });
    }

    const serviceModal = document.querySelector('#service-modal');
    const serviceOpenButton = document.querySelector('[data-open-service-modal]');
    const serviceEditButtons = document.querySelectorAll('[data-edit-service]');
    const serviceCloseButtons = document.querySelectorAll('[data-close-service-modal]');
    const serviceForm = document.querySelector('#service-form');
    const serviceVisualInputs = document.querySelectorAll('#service-modal input[name="visual_type"]');
    const serviceVisualPanels = document.querySelectorAll('[data-service-visual-panel]');
    const serviceModalTitle = document.querySelector('#service-modal-title');
    const serviceImageHelp = document.querySelector('[data-service-image-help]');
    let previousServiceFocus;

    const syncServiceVisualPanels = () => {
      const selectedType = document.querySelector('#service-modal input[name="visual_type"]:checked')?.value;
      const isEditing = serviceForm?.dataset.editMode === 'true';
      const currentVisualType = serviceForm?.dataset.currentVisualType;

      serviceVisualPanels.forEach((panel) => {
        const isSelected = panel.dataset.serviceVisualPanel === selectedType;
        panel.hidden = !isSelected;
        panel.querySelectorAll('input, textarea').forEach((input) => {
          input.disabled = !isSelected;
          input.required = isSelected && (
            input.name !== 'image' || !isEditing || currentVisualType !== 'image'
          );
        });
      });
    };

    const closeServiceModal = () => {
      if (!serviceModal) return;
      serviceModal.hidden = true;
      previousServiceFocus?.focus();
    };

    const openServiceModal = () => {
      if (!serviceModal) return;
      previousServiceFocus = document.activeElement;
      serviceModal.hidden = false;
      serviceModal.querySelector('#service-category')?.focus();
    };

    const setServiceMode = (isEditing, serviceId = '', visualType = 'icon') => {
      if (!serviceForm) return;

      const methodInput = serviceForm.querySelector('input[name="_method"]');
      const serviceIdInput = serviceForm.querySelector('input[name="service_id"]');
      const filterCategory = serviceForm.dataset.filterCategory;
      const query = filterCategory ? `?category_id=${encodeURIComponent(filterCategory)}` : '';

      serviceForm.dataset.editMode = String(isEditing);
      serviceForm.dataset.currentVisualType = visualType;
      serviceForm.action = isEditing
        ? `${serviceForm.dataset.updateBase}/${encodeURIComponent(serviceId)}${query}`
        : serviceForm.dataset.createAction;

      if (methodInput) {
        methodInput.disabled = !isEditing;
        methodInput.value = 'PUT';
      }
      if (serviceIdInput) serviceIdInput.value = isEditing ? serviceId : '';
      if (serviceModalTitle) serviceModalTitle.textContent = isEditing ? 'Edit service' : 'Add a service';
      if (serviceImageHelp) {
        serviceImageHelp.textContent = isEditing && visualType === 'image'
          ? 'Current image will be kept unless you upload a replacement. JPG, PNG, or WebP; maximum 2 MB.'
          : 'JPG, PNG, or WebP; maximum file size 2 MB.';
      }
      syncServiceVisualPanels();
    };

    if (serviceModal && serviceForm) {
      syncServiceVisualPanels();

      if (serviceForm.dataset.editMode === 'true') {
        const serviceId = serviceForm.querySelector('input[name="service_id"]')?.value;
        const selectedType = document.querySelector('#service-modal input[name="visual_type"]:checked')?.value || 'icon';
        setServiceMode(true, serviceId, selectedType);
      }

      serviceOpenButton?.addEventListener('click', () => {
        serviceForm.reset();
        const iconRadio = serviceForm.querySelector('input[name="visual_type"][value="icon"]');
        if (iconRadio) iconRadio.checked = true;
        const iconInput = serviceForm.querySelector('input[name="icon_class"]');
        if (iconInput) iconInput.value = 'fas fa-heart';
        const svgInput = serviceForm.querySelector('textarea[name="svg_code"]');
        if (svgInput) svgInput.value = '';
        const serviceIdInput = serviceForm.querySelector('input[name="service_id"]');
        if (serviceIdInput) serviceIdInput.value = '';
        setServiceMode(false);
        openServiceModal();
      });

      serviceEditButtons.forEach((button) => {
        button.addEventListener('click', () => {
          serviceForm.reset();
          const { serviceId, serviceName, categoryId, visualType, visualValue } = button.dataset;
          const nameInput = serviceForm.querySelector('input[name="name"]');
          const categoryInput = serviceForm.querySelector('select[name="category_id"]');
          const visualTypeInput = serviceForm.querySelector(`input[name="visual_type"][value="${visualType}"]`);

          if (nameInput) nameInput.value = serviceName || '';
          if (categoryInput) categoryInput.value = categoryId || '';
          if (visualTypeInput) visualTypeInput.checked = true;

          if (visualType === 'svg') {
            const svgInput = serviceForm.querySelector('textarea[name="svg_code"]');
            if (svgInput) svgInput.value = visualValue || '';
          } else if (visualType === 'icon') {
            const iconInput = serviceForm.querySelector('input[name="icon_class"]');
            if (iconInput) iconInput.value = visualValue || '';
          }

          setServiceMode(true, serviceId, visualType);
          openServiceModal();
        });
      });

      serviceCloseButtons.forEach((button) => {
        button.addEventListener('click', closeServiceModal);
      });

      serviceModal.addEventListener('click', (event) => {
        if (event.target === serviceModal) closeServiceModal();
      });

      serviceVisualInputs.forEach((input) => {
        input.addEventListener('change', syncServiceVisualPanels);
      });

      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !serviceModal.hidden) closeServiceModal();
      });
    }

    const doctorModal = document.querySelector('#doctor-modal');
    const doctorOpenButton = document.querySelector('[data-open-doctor-modal]');
    const doctorCloseButtons = document.querySelectorAll('[data-close-doctor-modal]');
    let previousDoctorFocus;

    const closeDoctorModal = () => {
      if (!doctorModal) return;
      doctorModal.hidden = true;
      previousDoctorFocus?.focus();
    };

    if (doctorModal) {
      const doctorForm = document.querySelector('#doctor-form');
      const doctorEditButtons = document.querySelectorAll('[data-edit-doctor]');
      const doctorModalTitle = document.querySelector('#doctor-modal-title');

      const setDoctorMode = (isEditing, doctorId = '') => {
        if (!doctorForm) return;

        const methodInput = doctorForm.querySelector('input[name="_method"]');
        const doctorIdInput = doctorForm.querySelector('input[name="doctor_id"]');
        doctorForm.dataset.editMode = String(isEditing);
        doctorForm.action = isEditing
          ? `${doctorForm.dataset.updateBase}/${encodeURIComponent(doctorId)}`
          : doctorForm.dataset.createAction;

        if (methodInput) {
          methodInput.disabled = !isEditing;
          methodInput.value = 'PUT';
        }
        if (doctorIdInput) doctorIdInput.value = isEditing ? doctorId : '';
        if (doctorModalTitle) doctorModalTitle.textContent = isEditing ? 'Edit doctor' : 'Register a doctor';
        const submitButton = doctorForm.querySelector('button[type="submit"]');
        if (submitButton) submitButton.textContent = isEditing ? 'Save changes' : 'Register doctor';
      };

      if (doctorForm?.dataset.editMode === 'true') {
        const doctorId = doctorForm.querySelector('input[name="doctor_id"]')?.value;
        setDoctorMode(true, doctorId);
      }

      doctorOpenButton?.addEventListener('click', () => {
        doctorForm?.reset();
        setDoctorMode(false);
        previousDoctorFocus = document.activeElement;
        doctorModal.hidden = false;
        doctorModal.querySelector('#doctor-name')?.focus();
      });

      doctorEditButtons.forEach((button) => {
        button.addEventListener('click', () => {
          if (!doctorForm) return;
          doctorForm.reset();
          const fields = {
            name: button.dataset.name,
            category_id: button.dataset.categoryId,
            hospital_id: button.dataset.hospitalId,
            email: button.dataset.email,
            phone: button.dataset.phone,
            website_url: button.dataset.website,
            qualification: button.dataset.qualification,
            experience_years: button.dataset.experience,
            address: button.dataset.address,
            bio: button.dataset.bio,
          };

          Object.entries(fields).forEach(([name, value]) => {
            const field = doctorForm.elements.namedItem(name);
            if (field) field.value = value || '';
          });

          setDoctorMode(true, button.dataset.doctorId);
          previousDoctorFocus = document.activeElement;
          doctorModal.hidden = false;
          doctorModal.querySelector('#doctor-name')?.focus();
        });
      });

      doctorCloseButtons.forEach((button) => {
        button.addEventListener('click', closeDoctorModal);
      });

      doctorModal.addEventListener('click', (event) => {
        if (event.target === doctorModal) closeDoctorModal();
      });

      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !doctorModal.hidden) closeDoctorModal();
      });
    }

    const hospitalModal = document.querySelector('#hospital-modal');
    const hospitalOpenButton = document.querySelector('[data-open-hospital-modal]');
    const hospitalCloseButtons = document.querySelectorAll('[data-close-hospital-modal]');
    let previousHospitalFocus;

    const closeHospitalModal = () => {
      if (!hospitalModal) return;
      hospitalModal.hidden = true;
      previousHospitalFocus?.focus();
    };

    if (hospitalModal) {
      const hospitalForm = document.querySelector('#hospital-form');
      const hospitalEditButtons = document.querySelectorAll('[data-edit-hospital]');
      const hospitalModalTitle = document.querySelector('#hospital-modal-title');

      const setHospitalMode = (isEditing, hospitalId = '') => {
        if (!hospitalForm) return;

        const methodInput = hospitalForm.querySelector('input[name="_method"]');
        const hospitalIdInput = hospitalForm.querySelector('input[name="hospital_id"]');
        hospitalForm.dataset.editMode = String(isEditing);
        hospitalForm.action = isEditing
          ? `${hospitalForm.dataset.updateBase}/${encodeURIComponent(hospitalId)}`
          : hospitalForm.dataset.createAction;

        if (methodInput) {
          methodInput.disabled = !isEditing;
          methodInput.value = 'PUT';
        }
        if (hospitalIdInput) hospitalIdInput.value = isEditing ? hospitalId : '';
        if (hospitalModalTitle) hospitalModalTitle.textContent = isEditing ? 'Edit hospital' : 'Register a hospital';
        const submitButton = hospitalForm.querySelector('button[type="submit"]');
        if (submitButton) submitButton.textContent = isEditing ? 'Save changes' : 'Register hospital';
      };

      if (hospitalForm?.dataset.editMode === 'true') {
        const hospitalId = hospitalForm.querySelector('input[name="hospital_id"]')?.value;
        setHospitalMode(true, hospitalId);
      }

      hospitalOpenButton?.addEventListener('click', () => {
        hospitalForm?.reset();
        setHospitalMode(false);
        previousHospitalFocus = document.activeElement;
        hospitalModal.hidden = false;
        hospitalModal.querySelector('#hospital-name')?.focus();
      });

      hospitalEditButtons.forEach((button) => {
        button.addEventListener('click', () => {
          if (!hospitalForm) return;
          hospitalForm.reset();
          const fields = {
            name: button.dataset.name,
            email: button.dataset.email,
            phone: button.dataset.phone,
            website_url: button.dataset.website,
            address: button.dataset.address,
            bio: button.dataset.bio,
          };

          Object.entries(fields).forEach(([name, value]) => {
            const field = hospitalForm.elements.namedItem(name);
            if (field) field.value = value || '';
          });

          setHospitalMode(true, button.dataset.hospitalId);
          previousHospitalFocus = document.activeElement;
          hospitalModal.hidden = false;
          hospitalModal.querySelector('#hospital-name')?.focus();
        });
      });

      hospitalCloseButtons.forEach((button) => {
        button.addEventListener('click', closeHospitalModal);
      });

      hospitalModal.addEventListener('click', (event) => {
        if (event.target === hospitalModal) closeHospitalModal();
      });

      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !hospitalModal.hidden) closeHospitalModal();
      });
    }

    const doctorCredentialsModal = document.querySelector('#doctor-credentials-modal');
    const doctorLoginButtons = document.querySelectorAll('[data-login-access]');
    const credentialCloseButtons = document.querySelectorAll('[data-close-doctor-credentials]');
    const credentialError = document.querySelector('#doctor-credentials-error');
    const credentialMessage = document.querySelector('#doctor-credentials-message');
    let previousCredentialsFocus;

    const closeCredentialsModal = () => {
      if (!doctorCredentialsModal) return;
      doctorCredentialsModal.hidden = true;
      document.querySelector('#doctor-generated-username').textContent = '';
      document.querySelector('#doctor-generated-password').textContent = '';
      document.querySelector('#doctor-credentials-message').textContent = '';
      if (credentialError) {
        credentialError.hidden = true;
        credentialError.textContent = '';
      }
      previousCredentialsFocus?.focus();
    };

    const showCredentialError = (message) => {
      if (!credentialError) return;
      credentialError.textContent = message;
      credentialError.hidden = false;
    };

    doctorLoginButtons.forEach((button) => {
      button.addEventListener('click', async () => {
      const loginMode = button.dataset.loginMode;
      if (
        loginMode === 'reset'
        && !window.confirm(`The old password is not recoverable. Create and save a new password for this ${button.dataset.entityLabel || 'account'}?`)
      ) {
        return;
      }

      const csrfToken = document.querySelector('[data-login-csrf] input[name="_token"]')?.value;
      if ((loginMode !== 'view' && !csrfToken) || !doctorCredentialsModal) {
        showCredentialError('Unable to securely start the request. Refresh the page and try again.');
        if (doctorCredentialsModal) doctorCredentialsModal.hidden = false;
        return;
      }

      button.disabled = true;
      button.innerHTML = loginMode === 'view'
        ? '<i class="fas fa-spinner fa-spin"></i> Loading...'
        : '<i class="fas fa-spinner fa-spin"></i> Creating...';
      let credentialsLoaded = false;

      try {
        const response = await fetch(button.dataset.endpoint, {
          method: loginMode === 'view' ? 'GET' : 'POST',
          credentials: 'same-origin',
          headers: loginMode === 'view'
            ? { 'Accept': 'application/json' }
            : { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        });
        const result = await response.json();

          if (!response.ok) {
            throw new Error(result.message || 'Could not create login access.');
          }

          previousCredentialsFocus = button;
          document.querySelector('#doctor-generated-username').textContent = result.username;
          document.querySelector('#doctor-generated-password').textContent = result.password;
          credentialMessage.textContent = result.message;
          if (credentialError) {
            credentialError.hidden = true;
            credentialError.textContent = '';
          }
          doctorCredentialsModal.hidden = false;
          doctorCredentialsModal.querySelector('[data-close-doctor-credentials]')?.focus();
          credentialsLoaded = true;
        } catch (error) {
          previousCredentialsFocus = button;
          doctorCredentialsModal.hidden = false;
          showCredentialError(error instanceof Error ? error.message : 'Could not create login access.');
        } finally {
          if (credentialsLoaded && loginMode !== 'view') {
            button.dataset.loginMode = 'view';
            button.dataset.endpoint = button.dataset.viewEndpoint;
            button.title = 'View the existing login credentials';
            button.innerHTML = '<i class="fas fa-eye"></i> View login';
          } else {
            button.disabled = false;
            button.innerHTML = loginMode === 'view'
              ? '<i class="fas fa-eye"></i> View login'
              : loginMode === 'reset'
                ? '<i class="fas fa-key"></i> Reset login'
                : '<i class="fas fa-key"></i> Login access';
          }
        }
      });
    });

    credentialCloseButtons.forEach((button) => {
      button.addEventListener('click', closeCredentialsModal);
    });

    doctorCredentialsModal?.addEventListener('click', (event) => {
      if (event.target === doctorCredentialsModal) closeCredentialsModal();
    });

    document.querySelectorAll('[data-copy-credential]').forEach((button) => {
      button.addEventListener('click', async () => {
        const targetId = button.dataset.copyCredential === 'username'
          ? '#doctor-generated-username'
          : '#doctor-generated-password';
        const value = document.querySelector(targetId)?.textContent;
        if (!value) return;

        try {
          await navigator.clipboard.writeText(value);
          button.title = 'Copied';
          window.setTimeout(() => { button.title = `Copy ${button.dataset.copyCredential}`; }, 1500);
        } catch (error) {
          showCredentialError('Clipboard access failed. Select and copy the credential manually.');
        }
      });
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && doctorCredentialsModal && !doctorCredentialsModal.hidden) {
        closeCredentialsModal();
      }
    });
  })();

    (() => {
      const apiTokenModal = document.querySelector('#api-token-modal');
      const apiTokenButtons = document.querySelectorAll('[data-api-access]');
      const apiToken = document.querySelector('#generated-api-token');
      const apiTokenError = document.querySelector('#api-token-error');
      let previousApiTokenFocus;

      const closeApiTokenModal = () => {
        if (!apiTokenModal) return;
        apiTokenModal.hidden = true;
        if (apiToken) apiToken.textContent = '';
        document.querySelector('#api-token-message').textContent = 'Existing API token. It will not be changed.';
        if (apiTokenError) {
          apiTokenError.hidden = true;
          apiTokenError.textContent = '';
        }
        previousApiTokenFocus?.focus();
      };

      const showApiTokenError = (message) => {
        if (!apiTokenError) return;
        apiTokenError.textContent = message;
        apiTokenError.hidden = false;
      };

      apiTokenButtons.forEach((button) => {
        button.addEventListener('click', async () => {
          const apiMode = button.dataset.apiMode;
          if (
            apiMode === 'create'
            && button.dataset.legacyToken === 'true'
            && !window.confirm('The old API token cannot be viewed or recovered. Creating a new token will replace it. Continue?')
          ) {
            return;
          }

          const csrfToken = apiMode === 'create'
            ? document.querySelector('[data-login-csrf] input[name="_token"]')?.value
            : null;
          if ((apiMode === 'create' && !csrfToken) || !apiTokenModal) {
            showApiTokenError('Unable to securely start the request. Refresh the page and try again.');
            if (apiTokenModal) apiTokenModal.hidden = false;
            return;
          }

          button.disabled = true;
          button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';

          try {
            const response = await fetch(button.dataset.endpoint, {
              method: apiMode === 'view' ? 'GET' : 'POST',
              credentials: 'same-origin',
              headers: apiMode === 'view'
                ? { 'Accept': 'application/json' }
                : { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            });
            const result = await response.json();

            if (!response.ok) {
              throw new Error(result.message || 'Could not create API access.');
            }

            previousApiTokenFocus = button;
            apiToken.textContent = result.token;
            document.querySelector('#api-token-message').textContent = result.message;
            apiTokenError.hidden = true;
            apiTokenError.textContent = '';
            apiTokenModal.hidden = false;
            apiTokenModal.querySelector('[data-close-api-token]')?.focus();
            button.disabled = false;
            button.dataset.apiMode = 'view';
            button.dataset.legacyToken = 'false';
            button.dataset.endpoint = button.dataset.viewEndpoint;
            button.title = 'View the existing API token';
            button.innerHTML = '<i class="fas fa-eye"></i> View API';
          } catch (error) {
            previousApiTokenFocus = button;
            apiTokenModal.hidden = false;
            showApiTokenError(error instanceof Error ? error.message : 'Could not create API access.');
            button.disabled = false;
            button.innerHTML = apiMode === 'view'
              ? '<i class="fas fa-eye"></i> View API'
              : '<i class="fas fa-code"></i> API access';
          }
        });
      });

      document.querySelectorAll('[data-close-api-token]').forEach((button) => {
        button.addEventListener('click', closeApiTokenModal);
      });

      apiTokenModal?.addEventListener('click', (event) => {
        if (event.target === apiTokenModal) closeApiTokenModal();
      });

      document.querySelector('[data-copy-api-token]')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;
        if (!apiToken?.textContent) return;

        try {
          await navigator.clipboard.writeText(apiToken.textContent);
          button.title = 'Copied';
          window.setTimeout(() => { button.title = 'Copy API token'; }, 1500);
        } catch (error) {
          showApiTokenError('Clipboard access failed. Select and copy the token manually.');
        }
      });

      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && apiTokenModal && !apiTokenModal.hidden) {
          closeApiTokenModal();
        }
      });
    })();
</script>
</body>
</html>
