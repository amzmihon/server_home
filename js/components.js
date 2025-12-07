function loadFragment(targetId, url, callback) {
    const target = document.getElementById(targetId);
    if (!target) return;

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Failed to load ${url}`);
            }
            return response.text();
        })
        .then(html => {
            target.innerHTML = html;
            if (typeof callback === 'function') {
                callback();
            }
        })
        .catch(error => {
            console.error(error);
        });
}

function setDesktopNavState(link, isActive) {
    if (!link) return;
    if (isActive) {
        link.classList.add('text-blue-600', 'font-semibold', 'border-b-2', 'border-blue-600');
        link.classList.remove('text-gray-700');
    } else {
        link.classList.remove('text-blue-600', 'font-semibold', 'border-b-2', 'border-blue-600');
        link.classList.add('text-gray-700');
    }
}

function setMobileNavState(link, isActive) {
    if (!link) return;
    if (isActive) {
        link.classList.add('bg-blue-50', 'text-blue-700');
        link.classList.remove('text-gray-700');
    } else {
        link.classList.remove('bg-blue-50', 'text-blue-700');
        link.classList.add('text-gray-700');
    }
}

function setServiceLinkState(link, isActive) {
    if (!link) return;
    if (isActive) {
        link.classList.add('text-blue-600', 'font-semibold');
        link.classList.remove('text-gray-700', 'text-gray-600');
    } else {
        link.classList.remove('text-blue-600', 'font-semibold');
        link.classList.add('text-gray-700');
        if (link.classList.contains('block') && link.classList.contains('text-base')) {
            link.classList.add('text-gray-600');
        }
    }
}

function setMobileServiceLinkState(link, isActive) {
    if (!link) return;
    if (isActive) {
        link.classList.add('bg-blue-50', 'text-blue-700', 'font-semibold');
        link.classList.remove('text-gray-600');
    } else {
        link.classList.remove('bg-blue-50', 'text-blue-700', 'font-semibold');
        link.classList.add('text-gray-600');
    }
}

function highlightNavigation() {
    const page = document.body.dataset.page || '';
    const service = document.body.dataset.service || '';

    const desktopLinks = document.querySelectorAll('[data-nav-link]');
    desktopLinks.forEach(link => {
        setDesktopNavState(link, link.dataset.navLink === page);
    });

    const mobileLinks = document.querySelectorAll('[data-mobile-nav-link]');
    mobileLinks.forEach(link => {
        setMobileNavState(link, link.dataset.mobileNavLink === page);
    });

    if (service) {
        const desktopServiceLinks = document.querySelectorAll('[data-service-link]');
        desktopServiceLinks.forEach(link => {
            setServiceLinkState(link, link.dataset.serviceLink === service);
        });

        const mobileServiceLinks = document.querySelectorAll('[data-mobile-service-link]');
        mobileServiceLinks.forEach(link => {
            setMobileServiceLinkState(link, link.dataset.mobileServiceLink === service);
        });
    }
}

function setupMobileMenu() {
    const menuBtn = document.getElementById('menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const menuIconOpen = document.getElementById('menu-icon-open');
    const menuIconClose = document.getElementById('menu-icon-close');

    if (!menuBtn || !mobileMenu) return;

    menuBtn.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
        if (menuIconOpen) {
            menuIconOpen.classList.toggle('hidden');
        }
        if (menuIconClose) {
            menuIconClose.classList.toggle('hidden');
        }
    });
}

function setupMobileServicesDropdown() {
    const servicesBtnMobile = document.getElementById('services-btn-mobile');
    const servicesDropdownMobile = document.getElementById('services-dropdown-mobile');

    if (!servicesBtnMobile || !servicesDropdownMobile) return;

    servicesBtnMobile.addEventListener('click', (event) => {
        event.preventDefault();
        servicesDropdownMobile.classList.toggle('hidden');
    });
}

function initNavigation() {
    setupMobileMenu();
    setupMobileServicesDropdown();
    highlightNavigation();
}

document.addEventListener('DOMContentLoaded', () => {
    loadFragment('site-nav', 'partials/nav.html', initNavigation);
    loadFragment('site-footer', 'partials/footer.html');
});
