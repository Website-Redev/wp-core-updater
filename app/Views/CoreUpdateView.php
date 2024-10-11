<?php
namespace App\Views;

use Timber\Timber;

class CoreUpdateView{

    public function renderAdminPageView($context) {
        Timber::render('@admin/core-update.twig', $context);
    }
}

