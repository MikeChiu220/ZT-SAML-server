<?php

namespace SimpleSAML\Module\fidoauth;

use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialUserEntity;
use SimpleSAML\Database;

class CredentialSourceRepository implements PublicKeyCredentialSourceRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource
    {
        $stmt = $this->db->read(
            'SELECT * FROM fido_credentials WHERE credential_id = :credential_id',
            ['credential_id' => base64_encode($publicKeyCredentialId)]
        );

        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->hydrateCredentialSource($row);
    }

    public function findAllForUserEntity(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array
    {
        $stmt = $this->db->read(
            'SELECT * FROM fido_credentials WHERE user_handle = :user_handle',
            ['user_handle' => $publicKeyCredentialUserEntity->getId()]
        );

        $credentials = [];
        while ($row = $stmt->fetch()) {
            $credentials[] = $this->hydrateCredentialSource($row);
        }

        return $credentials;
    }

    public function saveCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource): void
    {
        $data = [
            'credential_id' => base64_encode($publicKeyCredentialSource->getPublicKeyCredentialId()),
            'type' => $publicKeyCredentialSource->getType(),
            'transports' => json_encode($publicKeyCredentialSource->getTransports()),
            'attestation_type' => $publicKeyCredentialSource->getAttestationType(),
            'trust_path' => json_encode($publicKeyCredentialSource->getTrustPath()->jsonSerialize()),
            'aaguid' => $publicKeyCredentialSource->getAaguid()->toString(),
            'public_key' => base64_encode($publicKeyCredentialSource->getCredentialPublicKey()),
            'user_handle' => $publicKeyCredentialSource->getUserHandle(),
            'counter' => $publicKeyCredentialSource->getCounter(),
        ];

        $this->db->write(
            'INSERT INTO fido_credentials 
            (credential_id, type, transports, attestation_type, trust_path, aaguid, public_key, user_handle, counter) 
            VALUES 
            (:credential_id, :type, :transports, :attestation_type, :trust_path, :aaguid, :public_key, :user_handle, :counter)
            ON DUPLICATE KEY UPDATE
            type = VALUES(type),
            transports = VALUES(transports),
            attestation_type = VALUES(attestation_type),
            trust_path = VALUES(trust_path),
            aaguid = VALUES(aaguid),
            public_key = VALUES(public_key),
            counter = VALUES(counter)',
            $data
        );
    }

    private function hydrateCredentialSource(array $row): PublicKeyCredentialSource
    {
        return new PublicKeyCredentialSource(
            base64_decode($row['credential_id']),
            $row['type'],
            json_decode($row['transports'], true),
            $row['attestation_type'],
            json_decode($row['trust_path'], true),
            \Webauthn\TrustPath\EmptyTrustPath::create(), // You might want to implement proper trust path deserialization
            \Ramsey\Uuid\Uuid::fromString($row['aaguid']),
            base64_decode($row['public_key']),
            $row['user_handle'],
            $row['counter']
        );
    }
}