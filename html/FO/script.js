// Script pour la transparence du header au défilement
document.addEventListener('DOMContentLoaded', function() {
    const header = document.querySelector('header');
    const scrollThreshold = 50; 
    if (header) { 
        window.addEventListener('scroll', function() {
            const scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollPosition > scrollThreshold) {
                header.classList.add('header-scrolled');
            } else {
                header.classList.remove('header-scrolled');
            }
        });
    }

    // MENU HAMBURGER
    const hamburgerButton = document.querySelector('.hamburger-menu');
    const mobileNav = document.querySelector('.mobile-nav-links');
    const pageBody = document.querySelector('body');

    if (hamburgerButton && mobileNav) {
        hamburgerButton.addEventListener('click', function() {
            mobileNav.classList.toggle('active');
            const isExpanded = mobileNav.classList.contains('active');
            hamburgerButton.setAttribute('aria-expanded', isExpanded);
            
            // Optionnel: Changer l'icône du hamburger en croix et vice-versa
            const icon = hamburgerButton.querySelector('i');
            if (isExpanded) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
                // Optionnel: Empêcher le scroll du body quand le menu est ouvert
                // pageBody.style.overflow = 'hidden'; 
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
                // Optionnel: Rétablir le scroll du body
                // pageBody.style.overflow = '';
            }
        });

        // Optionnel: Fermer le menu si on clique en dehors (sur un overlay par exemple)
        // ou sur un lien du menu
        mobileNav.addEventListener('click', function(event) {
            if (event.target.tagName === 'A') { // Si un lien dans le menu est cliqué
                mobileNav.classList.remove('active');
                hamburgerButton.setAttribute('aria-expanded', 'false');
                const icon = hamburgerButton.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
                // pageBody.style.overflow = '';
            }
        });
    }
});