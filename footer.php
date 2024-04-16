<footer class="footer">
    <!-- like shit -->
    <div class="container-fluid">
        <div class="row align-items-end justify-content-end">
            <div class="col-lg-6 mb-lg-0 mb-4">
                <div class="copyright text-end text-sm text-muted text-lg-end">
                © 2024,
                made with <i class="fa fa-heart"></i> by TIP
                </div>
            </div>
        </div>
    </div>
</footer>

<script>
    // Function to update main content width and margin-left
    function updateMainContentWidth() {
        // Get the offcanvas element
        var offcanvas = document.querySelector('.offcanvas');

        // Get the computed width of the offcanvas menu including margin
        var offcanvasWidth = offcanvas.offsetWidth;

        // Calculate the width of the main content
        var mainContentWidth = `calc(100% - ${offcanvasWidth}px)`;

        // Apply the calculated width to the main content
        var mainContent = document.querySelector('.main-content');
        mainContent.style.width = mainContentWidth;

        // Update margin-left of main content to accommodate offcanvas menu
        var offcanvasMarginLeft = window.getComputedStyle(offcanvas).getPropertyValue('margin-left');
        mainContent.style.marginLeft = offcanvasWidth + 'px';
    }

    // Update main content width when the window is resized
    window.addEventListener('resize', updateMainContentWidth);

    // Update main content width when the offcanvas menu is shown or hidden
    document.querySelector('.offcanvas').addEventListener('shown.bs.offcanvas', updateMainContentWidth);
    document.querySelector('.offcanvas').addEventListener('hidden.bs.offcanvas', updateMainContentWidth);

    // Initial update of main content width
    updateMainContentWidth();
</script>