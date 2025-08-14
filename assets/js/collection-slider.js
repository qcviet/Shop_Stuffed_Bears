document.addEventListener('DOMContentLoaded', function() {
    const track = document.querySelector('.collection-slider__track');
    const slides = document.querySelectorAll('.collection-slider__image');
    const nextButton = document.querySelector('.collection-slider__button.next');
    const prevButton = document.querySelector('.collection-slider__button.prev');
    
    const totalSlides = slides.length;
    const slidesPerGroup = 4;
    const totalGroups = Math.ceil(totalSlides / slidesPerGroup);
    let currentGroup = 0;

    // Initially disable prev button
    prevButton.disabled = true;

    function updateSlider() {
        const slideWidth = slides[0].offsetWidth;
        const gapWidth = 20; // gap between slides
        const moveAmount = (slideWidth + gapWidth) * slidesPerGroup;
        track.style.transform = `translateX(-${currentGroup * moveAmount}px)`;
        
        // Update button states
        prevButton.disabled = currentGroup === 0;
        nextButton.disabled = currentGroup === totalGroups - 1;
    }

    nextButton.addEventListener('click', () => {
        if (currentGroup < totalGroups - 1) {
            currentGroup++;
            updateSlider();
        }
    });

    prevButton.addEventListener('click', () => {
        if (currentGroup > 0) {
            currentGroup--;
            updateSlider();
        }
    });

    // Handle window resize
    window.addEventListener('resize', () => {
        currentGroup = 0;
        updateSlider();
    });
});
