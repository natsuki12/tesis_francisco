<?php
// Default values if not provided
$currentStep = $currentStep ?? 1;

$steps = [
    1 => 'Términos y condiciones',
    2 => 'Datos básicos',
    3 => 'Verificación de código',
    4 => 'Datos personales'
];
?>
<div class="pr-wrapper">
    <div class="pr-progress-bar">
        <!-- Connecting Line Background -->
        <div class="pr-line-background"></div>
        
        <?php foreach ($steps as $num => $label): ?>
            <?php 
                // Determine status
                if ($num < $currentStep) {
                    $statusClass = 'pr-step--completed';
                } elseif ($num == $currentStep) {
                    $statusClass = 'pr-step--active';
                } else {
                    $statusClass = 'pr-step--pending';
                }
            ?>
            <div class="pr-step <?= $statusClass ?>">
                <div class="pr-circle">
                    <?php if ($num < $currentStep): ?>
                        <!-- Check Icon for completed -->
                        <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    <?php else: ?>
                        <?= $num ?>
                    <?php endif; ?>
                </div>
                <div class="pr-label"><?= htmlspecialchars($label) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
