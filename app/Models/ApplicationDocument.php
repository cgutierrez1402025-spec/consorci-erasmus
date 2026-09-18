<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationDocument extends Model
{
    public const ID_DOCUMENT = 'id_document';

    public const HEALTH_CARD = 'health_card';

    public const EUROPASS_CV = 'europass_cv';

    public const MOTIVATION_LETTER = 'motivation_letter';

    public const LANGUAGE_PASSPORT = 'language_passport';

    public const CRIMINAL_RECORD_CERTIFICATE = 'criminal_record_certificate';

    public const BANK_ACCOUNT_OWNERSHIP = 'bank_account_ownership';

    public const DEPOSIT_RECEIPT = 'deposit_receipt';

    public const CRIMINAL_BACKGROUND_CERTIFICATE = 'criminal_background_certificate';

    public const SEXUAL_OFFENCES_CERTIFICATE = 'sexual_offences_certificate';

    public const MINOR_AUTHORIZATION = 'minor_authorization';

    public const FINAL_REPORT = 'final_report';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROVIDED = 'provided';

    public const STATUS_VALIDATED = 'validated';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = ['application_id', 'document_type', 'status', 'file_path', 'validated_at', 'validated_by', 'observations'];

    protected $casts = ['validated_at' => 'datetime'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public static function baseRequiredTypes(): array
    {
        return [self::ID_DOCUMENT, self::HEALTH_CARD, self::EUROPASS_CV, self::MOTIVATION_LETTER, self::LANGUAGE_PASSPORT];
    }

    public static function labels(): array
    {
        return [
            self::ID_DOCUMENT => 'DNI, NIE o pasaporte',
            self::HEALTH_CARD => 'Tarjeta sanitaria europea',
            self::EUROPASS_CV => 'CV Europass en inglés',
            self::MOTIVATION_LETTER => 'Carta de motivación en inglés',
            self::LANGUAGE_PASSPORT => 'Pasaporte de lenguas Europass',
            self::CRIMINAL_RECORD_CERTIFICATE => 'Certificado de delitos penales y sexuales',
            self::BANK_ACCOUNT_OWNERSHIP => 'Titularidad bancaria',
            self::DEPOSIT_RECEIPT => 'Resguardo de fianza',
            self::CRIMINAL_BACKGROUND_CERTIFICATE => 'Certificado de antecedentes penales',
            self::SEXUAL_OFFENCES_CERTIFICATE => 'Certificado de delitos sexuales',
            self::MINOR_AUTHORIZATION => 'Autorización para menores',
            self::FINAL_REPORT => 'Memoria final',
        ];
    }

    public static function requiresCriminalRecordCertificate(?string $program): bool
    {
        return str_contains(mb_strtolower($program ?? ''), 'sociosanit');
    }
}
