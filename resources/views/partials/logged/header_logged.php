<?php
// Placeholders para imágenes
$avatarImg = $avatarImg ?? asset('img/brand/avatar.png');
$phAvatar = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgdmlld0JveD0iMCAwIDQwIDQwIj4KICA8Y2lyY2xlIGN4PSIyMCIgY3k9IjIwIiByPSIyMCIgZmlsbD0iI2UwZTdmZiIvPgogIDxwYXRoIGQ9Ik0yMCAxOGE0IDQgMCAxIDAtNC00IDQgNCAwIDAgMCA0IDRabTAgMmMtNCA0LTggNi04IDh2Mmg2di0yYzAtMiA0LTQgOC04WiIgZmlsbD0iIzBiNWFhNiIvPgo8L3N2Zz4=';
?>
<!-- HEADER -->
<header class="sim-header">
    <div class="sim-header__left">
        <button class="sim-hamburger" id="sidebarToggle" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
    
    <div class="sim-header__right">
        <div class="sim-user">
            <img 
                class="sim-user__avatar" 
                src="<?= $avatarImg ?>" 
                alt="Avatar"
                onerror="this.onerror=null;this.src='<?= $phAvatar ?>';"
            >
            <span class="sim-user__name"><?= isset($user['name']) ? htmlspecialchars($user['name']) : 'Usuario' ?></span>
        </div>
    </div>
</header>