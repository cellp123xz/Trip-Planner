document.addEventListener('DOMContentLoaded', function() {
    // Initialize all Bootstrap tooltips
    try {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    } catch (e) {
        console.warn('Bootstrap tooltip initialization failed:', e);
    }
    
    // Initialize all Bootstrap popovers
    try {
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        const popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    } catch (e) {
        console.warn('Bootstrap popover initialization failed:', e);
    }
    
    // Fix for navbar toggler on mobile
    const navbarToggler = document.querySelector('.navbar-toggler');
    if (navbarToggler) {
        navbarToggler.addEventListener('click', function() {
            const target = document.querySelector(this.getAttribute('data-bs-target'));
            if (target) {
                target.classList.toggle('show');
            }
        });
    }
    
    // Handle "Get Started" button and other action buttons
    const actionButtons = document.querySelectorAll('.btn-primary, .btn-outline-primary, .btn-success');
    actionButtons.forEach(button => {
        if (button.tagName === 'A' && button.getAttribute('href')) {
            // It's already a link, make sure it works
            button.addEventListener('click', function(event) {
                const href = this.getAttribute('href');
                // Don't interfere with normal link behavior unless needed
                if (href.startsWith('#') && href.length > 1) {
                    event.preventDefault();
                    const targetElement = document.querySelector(href);
                    if (targetElement) {
                        targetElement.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
        } else if (button.tagName === 'BUTTON') {
            // It's a button, handle form submission or other actions
            button.addEventListener('click', function(event) {
                if (this.type === 'submit' && this.form) {
                    // Let the form handle submission
                } else if (this.dataset.action) {
                    // Custom action handling
                    handleCustomAction(this.dataset.action, this.dataset);
                }
            });
        }
    });
    
    // Handle form submissions with validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Highlight all invalid fields
                const invalidFields = form.querySelectorAll(':invalid');
                invalidFields.forEach(field => {
                    field.classList.add('is-invalid');
                    
                    // Add event listener to remove invalid class when user starts typing
                    field.addEventListener('input', function() {
                        this.classList.remove('is-invalid');
                    }, { once: true });
                });
            }
            
            form.classList.add('was-validated');
        });
    });
    
    // Custom function to handle special actions
    function handleCustomAction(action, data) {
        switch(action) {
            case 'scrollTo':
                const targetElement = document.querySelector(data.target);
                if (targetElement) {
                    targetElement.scrollIntoView({ behavior: 'smooth' });
                }
                break;
            case 'toggleSection':
                const section = document.querySelector(data.section);
                if (section) {
                    section.classList.toggle('d-none');
                }
                break;
        }
    }
    
    // Log to console when loaded
    console.log('TripPlanner JS initialized successfully!');
});
