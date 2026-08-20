document.addEventListener('DOMContentLoaded', function() {
    const langSelectedAll = document.querySelectorAll('.header__lang-selected');
    const searchOpen = document.querySelector('.header__search-open');
    const searchBlock = document.querySelector('.header__search');
    const headerBusket = document.querySelector('.header__busket');
    const busket = document.querySelector('.busket');
    headerBusket.addEventListener('click', () => {
        busket.classList.toggle('active');
    });
    langSelectedAll.forEach(langSelected => {
        const langBlock = langSelected.closest('.header__lang'); 
        langSelected.addEventListener('click', function(e) {
            e.preventDefault();
            langBlock.classList.toggle('active');
        });
        document.addEventListener('click', function(e) {
            if (!langBlock.contains(e.target)) {
                langBlock.classList.remove('active');
            }
        });
    });

    if (searchOpen && searchBlock) {
        searchOpen.addEventListener('click', function(e) {
            e.preventDefault();
            searchBlock.classList.toggle('active');
        });
        document.addEventListener('click', function(e) {
            if (!searchBlock.contains(e.target)) {
                searchBlock.classList.remove('active');
            }

            if (busket.classList.contains('active') && !busket.contains(e.target) && !headerBusket.contains(e.target)) {
                busket.classList.remove('active');
            }
        });
    }
});

const headerBurger = document.querySelector('.header__burger');
const headerMenu = document.querySelector('.header__menu');
const headerMenuClose = document.querySelector('.header__menu-close');
headerBurger.addEventListener('click', () => {
    headerMenu.classList.add('active');
});
headerMenuClose.addEventListener('click', () => {
    headerMenu.classList.remove('active');
});
