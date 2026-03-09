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
const formMessage = document.getElementById('formMessage');

contactForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(contactForm);
    const data = {
        name: formData.get('name'),
        phone: formData.get('phone'),
        email: formData.get('email'),
        comment: formData.get('comment')
    };

    const apiEndpoint = '/api/profile';
    
    // Show loading state
    const submitBtn = contactForm.querySelector('.submit-btn');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Отправка...';
    submitBtn.disabled = true;
    formMessage.style.display = 'none';

    try {
        const response = await fetch(apiEndpoint, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json().catch(() => ({}));

        if (response.status === 201 && result.ok) {
            formMessage.textContent = `Данные сохранены. Логин: ${result.login}, пароль: ${result.password}. Профиль: ${result.profileUrl}`;
            formMessage.className = 'form-message success';
            contactForm.reset();
        } else if (response.status === 422 && result && result.errors) {
            const msgs = Object.values(result.errors);
            formMessage.textContent = msgs.join(' ');
            formMessage.className = 'form-message error';
        } else if (response.status === 401) {
            formMessage.textContent = 'Требуется авторизация.';
            formMessage.className = 'form-message error';
        } else {
            throw new Error('Ошибка отправки формы');
        }
    } catch (error) {
        console.error('Form submission error:', error);
        formMessage.textContent = 'Произошла ошибка при отправке формы. Пожалуйста, попробуйте еще раз или свяжитесь с нами по телефону.';
        formMessage.className = 'form-message error';
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

