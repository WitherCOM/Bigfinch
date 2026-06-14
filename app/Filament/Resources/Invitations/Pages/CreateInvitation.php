<?php

namespace App\Filament\Resources\Invitations\Pages;

use App\Filament\Resources\Invitations\InvitationResource;
use App\Mail\InvitationMail;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;

class CreateInvitation extends CreateRecord
{
    protected static string $resource = InvitationResource::class;

    protected function afterCreate(): void
    {
        Mail::to($this->record->email)->send(new InvitationMail($this->record));
    }
}
