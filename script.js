// Mobile Menu Toggle
const burgerMenu = document.getElementById('burgerMenu');
const nav = document.getElementById('nav');
const dropdowns = document.querySelectorAll('.dropdown');

burgerMenu.addEventListener('click', () => {
    nav.classList.toggle('active');
    burgerMenu.classList.toggle('active');
});

// Mobile Dropdown Toggle
dropdowns.forEach(dropdown => {
    const link = dropdown.querySelector('.nav-link');
    link.addEventListener('click', (e) => {
        if (window.innerWidth <= 768) {
            e.preventDefault();
            dropdown.classList.toggle('active');
        }
    });
});

// Close menu when clicking outside
document.addEventListener('click', (e) => {
    if (window.innerWidth <= 768) {
        if (!nav.contains(e.target) && !burgerMenu.contains(e.target)) {
            nav.classList.remove('active');
            dropdowns.forEach(drop => drop.classList.remove('active'));
        }
    }
});

// Smooth Scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href !== '#') {
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                const headerHeight = document.querySelector('.header').offsetHeight;
                const targetPosition = target.offsetTop - headerHeight;
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                // Close mobile menu after click
                if (window.innerWidth <= 768) {
                    nav.classList.remove('active');
                    dropdowns.forEach(drop => drop.classList.remove('active'));
                }
            }
        }
    });
});

// Tariff Cards - Show button on click for mobile
const tariffCards = document.querySelectorAll('.tariff-card');
tariffCards.forEach(card => {
    card.addEventListener('click', (e) => {
        if (window.innerWidth <= 768 && !e.target.classList.contains('tariff-btn')) {
            // Remove active class from all cards
            tariffCards.forEach(c => c.classList.remove('active'));
            // Add active class to clicked card
            card.classList.add('active');
        }
    });
});

// Tariff Button Click - Scroll to Contacts
const tariffButtons = document.querySelectorAll('.tariff-btn');
tariffButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const contactsSection = document.getElementById('contacts');
        const headerHeight = document.querySelector('.header').offsetHeight;
        const targetPosition = contactsSection.offsetTop - headerHeight;
        window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
        });
        // Pre-fill comment with tariff name
        const commentField = document.getElementById('comment');
        const tariffName = btn.getAttribute('data-tariff');
        commentField.value = `Интересуюсь тарифом: ${tariffName}`;
        commentField.focus();
    });
});

// Reviews Carousel
let currentReview = 0;
const reviewCards = document.querySelectorAll('.review-card');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');

function showReview(index) {
    reviewCards.forEach(card => card.classList.remove('active'));
    if (index < 0) {
        currentReview = reviewCards.length - 1;
    } else if (index >= reviewCards.length) {
        currentReview = 0;
    } else {
        currentReview = index;
    }
    reviewCards[currentReview].classList.add('active');
}

prevBtn.addEventListener('click', () => {
    showReview(currentReview - 1);
});

nextBtn.addEventListener('click', () => {
    showReview(currentReview + 1);
});

// Auto-play carousel (optional)
let carouselInterval;
function startCarousel() {
    carouselInterval = setInterval(() => {
        showReview(currentReview + 1);
    }, 5000);
}

function stopCarousel() {
    clearInterval(carouselInterval);
}

// Start carousel
startCarousel();

// Pause on hover
const reviewsCarousel = document.querySelector('.reviews-carousel');
reviewsCarousel.addEventListener('mouseenter', stopCarousel);
reviewsCarousel.addEventListener('mouseleave', startCarousel);

// Form Submission
const contactForm = document.getElementById('contactForm');
let formMessage = document.getElementById('formMessage');

if (contactForm && !formMessage) {
    formMessage = document.createElement('div');
    formMessage.className = 'form-message';
    formMessage.id = 'formMessage';
    contactForm.appendChild(formMessage);
}

function clearFieldErrors(form) {
    form.querySelectorAll('[data-field-error]').forEach((el) => el.remove());
}

function showFieldErrors(form, fields) {
    if (!fields) return;
    Object.keys(fields).forEach((name) => {
        const input = form.querySelector(`[name="${CSS.escape(name)}"]`);
        if (!input) return;

        const group = input.closest('.form-group') || input.parentElement;
        const err = document.createElement('div');
        err.setAttribute('data-field-error', '1');
        err.style.color = '#c0392b';
        err.style.fontSize = '.85rem';
        err.textContent = fields[name];
        group.appendChild(err);
    });
}

if (contactForm) contactForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(contactForm);
    const data = {
        name: formData.get('name'),
        phone: formData.get('phone'),
        email: formData.get('email'),
        comment: formData.get('comment')
    };

    const endpoint = 'landing_submit.php';
    
    // Show loading state
    const submitBtn = contactForm.querySelector('.submit-btn');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Отправка...';
    submitBtn.disabled = true;
    if (formMessage) formMessage.style.display = 'none';
    clearFieldErrors(contactForm);

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
            },
            body: formData
        });

        const result = await response.json().catch(() => null);
        console.log('[lead submit]', response.status, result);

        if (response.ok && result && result.ok) {
            let msg = 'Спасибо! Ваша заявка успешно отправлена. Мы свяжемся с вами в ближайшее время.';
            if (result.credentials && result.credentials.login && result.credentials.password) {
                msg += `\n\nЗапомните: логин ${result.credentials.login}, пароль ${result.credentials.password}.`;
            }

            if (formMessage) {
                formMessage.textContent = msg;
                formMessage.className = 'form-message success';
                formMessage.style.display = 'block';
            }
            contactForm.reset();
            return;
        }

        if (response.status === 422 && result && result.fields) {
            if (formMessage) {
                formMessage.textContent = result.error || 'Исправьте ошибки в форме.';
                formMessage.className = 'form-message error';
                formMessage.style.display = 'block';
            }
            showFieldErrors(contactForm, result.fields);
            return;
        }

        throw new Error((result && result.error) || 'Ошибка отправки формы');
    } catch (error) {
        console.error('Form submission error:', error);
        if (formMessage) {
            formMessage.textContent = 'Произошла ошибка при отправке формы. Пожалуйста, попробуйте еще раз или свяжитесь с нами по телефону.';
            formMessage.className = 'form-message error';
            formMessage.style.display = 'block';
        }
    } finally {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }
});

// Dropdown menu hover effect for desktop
if (window.innerWidth > 768) {
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('mouseenter', () => {
            dropdown.classList.add('active');
        });
        dropdown.addEventListener('mouseleave', () => {
            dropdown.classList.remove('active');
        });
    });
}

// Handle window resize
window.addEventListener('resize', () => {
    if (window.innerWidth > 768) {
        nav.classList.remove('active');
        dropdowns.forEach(drop => drop.classList.remove('active'));
    }
});
