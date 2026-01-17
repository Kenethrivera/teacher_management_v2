document.addEventListener('DOMContentLoaded', () => {
    // Select all sections and nav links
    const sections = document.querySelectorAll('.section');
    const navLinks = document.querySelectorAll('.nav-link');

    // 1. Helper function to handle the switching logic
    function setActiveSection(targetId) {
        // SAFETY: If target doesn't exist (e.g. bad ID), default to dashboard
        if (!document.getElementById(targetId)) {
            targetId = 'dashboard';
        }

        // A. REMOVE 'active' class from ALL sections and links
        sections.forEach(sec => sec.classList.remove('active'));
        navLinks.forEach(link => link.classList.remove('active'));

        // B. ADD 'active' class to the specific target section
        const targetSection = document.getElementById(targetId);
        if (targetSection) {
            targetSection.classList.add('active');
        }

        // C. ADD 'active' class to the specific nav link
        const targetLink = document.querySelector(`.nav-link[data-section="${targetId}"]`);
        if (targetLink) {
            targetLink.classList.add('active');
        }

        // D. Save to storage
        localStorage.setItem('lastSection', targetId);
    }

    // 2. ON LOAD: Check LocalStorage or default to 'dashboard'
    const storedSection = localStorage.getItem('lastSection') || 'dashboard';

    // Force the switch immediately on load to prevent double-display
    setActiveSection(storedSection);

    // 3. CLICK HANDLERS: Attach to all navigation links
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = link.getAttribute('data-section');
            setActiveSection(targetId);
        });
    });
});