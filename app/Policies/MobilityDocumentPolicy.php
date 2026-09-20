<?php

namespace App\Policies;

use App\Models\MobilityDocument;
use App\Models\User;

class MobilityDocumentPolicy
{
    public function view(User $user, MobilityDocument $document): bool
    {
        return $user->canValidateMobilityDocuments()
            || $document->mobility->user_id === $user->id
            || $document->mobility->application?->user_id === $user->id;
    }

    public function upload(User $user, MobilityDocument $document): bool
    {
        return $this->view($user, $document);
    }

    public function validate(User $user, MobilityDocument $document): bool
    {
        return $user->canValidateMobilityDocuments();
    }
}
