<div class="offcanvas offcanvas-start bg-body-tertiary" data-bs-scroll="true" data-bs-backdrop="false" tabindex="-1" id="offcanvasScrolling" aria-labelledby="offcanvasScrollingLabel" style="width: fit-content !important;">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">
            <img src="images/logo-bt.png" style="height: 50px; display: inline-block; vertical-align: middle;" alt="Logo" >
            <span class="button-label navbar-brand black-ops-one-regular" href="dashboard.php" >  
                TIP LOGISTICS
            </span>
        </h5>
        <!-- <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button> -->
    </div>
    <hr class="gradient-line"  style="margin-top: 0; padding-top: 0; ">
    <div class="offcanvas-body">
        <div class="d-flex flex-column align-items-start p-0">
            <div class="btn-group-vertical gap-2 w-100" role="group" aria-label="Custom Radio Buttons">
                <button type="button" class="btn btn-transparent d-flex justify-content-start align-items-center rounded-3 p-0" aria-pressed="false" onclick="toggleButton(this)">
                    <div class="btn-icon" aria-pressed="false" >
                        <i class="fa-solid fa-shop"></i>
                    </div>
                    <span class="button-label">Dashboard</span>
                </button>
                <button type="button" class="btn btn-transparent d-flex justify-content-start align-items-center rounded-3 p-0" aria-pressed="false" onclick="toggleButton(this)">
                    <div class="btn-icon">
                        <i class="fa-solid fa-cubes" aria-pressed="false"></i>
                    </div>
                    <span class="button-label">Items</span>
                </button>
                <button type="button" class="btn btn-transparent d-flex justify-content-start align-items-center rounded-3 p-0" aria-pressed="false" onclick="toggleButton(this)">
                    <div class="btn-icon">
                        <i class="fa-solid fa-calendar" aria-pressed="false"></i>
                    </div>
                    <span class="button-label">Schedule</span>
                </button>
                <button type="button" class="btn btn-transparent d-flex justify-content-start align-items-center rounded-3 p-0" aria-pressed="false" onclick="toggleButton(this)">
                    <div class="btn-icon">
                        <i class="fa-solid fa-truck-fast" aria-pressed="false"></i>
                    </div>
                    <span class="button-label">Trucks</span>
                </button>
                <button type="button" class="btn btn-transparent d-flex justify-content-start align-items-center rounded-3 p-0" aria-pressed="false" onclick="toggleButton(this)">
                    <div class="btn-icon">
                        <i class="fa-solid fa-person-praying" aria-pressed="false"></i>
                    </div>
                    <span class="button-label">Staffs</span>
                </button>
                <button type="button" class="btn btn-transparent d-flex justify-content-start align-items-center rounded-3 p-0" aria-pressed="false" onclick="toggleButton(this)">
                    <div class="btn-icon">
                    
                        <i class="fa-solid fa-coins" aria-pressed="false"></i>
                    </div>
                    <span class="button-label">Billing</span>
                </button>
                <button type="button" class="btn btn-transparent d-flex justify-content-start align-items-center rounded-3 p-0" aria-pressed="false" onclick="toggleButton(this)">
                    <div class="btn-icon">
                    
                        <i class="fa-solid fa-right-from-bracket" aria-pressed="false"></i>
                    </div>
                    <span class="button-label">Logout</span>
                </button>
                <!-- <form action="" method="POST"> -->
                <!-- <button type="submit" name="logout" class="btn btn-transparent d-flex justify-content-start align-items-center rounded-3 p-0" aria-pressed="false">
                    <div class="btn-icon">
                        <i class="fa-solid fa-right-from-bracket" aria-pressed="false"></i>
                    </div>
                    <span class="button-label">Sign Out</span>
                </button> -->
                <!-- </form> -->
            </div>
        </div>
    </div>
</div>
<script>
    var offcanvasElement = document.getElementById('offcanvasScrolling');
    var offcanvas = new bootstrap.Offcanvas(offcanvasElement);
    offcanvas.show(); // Show the offcanvas when the page loads

    // Get the current page name without the file extension
    var currentPage = window.location.pathname.split('/').pop().replace('.php', '').replace(/_/g, ' ');

    // Find the button corresponding to the current page and mark it as active
    var buttons = document.querySelectorAll('.btn');
    buttons.forEach(function(button) {
        var buttonText = button.querySelector('.button-label').textContent.trim();
        if (buttonText.toLowerCase() === currentPage.toLowerCase()) {
            button.classList.add('active');
            button.querySelector('.btn-icon').classList.add('active');
        }
    });

    function toggleButton(button) {
        var buttons = button.parentNode.querySelectorAll('.btn');
        buttons.forEach(function(btn) {
            btn.classList.remove('active');
            btn.setAttribute('aria-pressed', 'false');
            btn.querySelector('.btn-icon').classList.remove('active');
        });
        button.classList.add('active');
        button.setAttribute('aria-pressed', 'true');
        button.querySelector('.btn-icon').classList.add('active');
        
        var buttonText = button.querySelector('.button-label').textContent.trim();
        var pageName = buttonText.replace(/\s+/g, '_').toLowerCase() + '.php';
        window.location.href = pageName;
        
        // Check window width before hiding the offcanvas
        // if (window.innerWidth <= 992) {
        //     offcanvas.hide(); // Close the offcanvas when a menu item is clicked and window size is small
        // }
    }
</script>
