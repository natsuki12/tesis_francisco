<?php
namespace App\Modules\Auth\Models;

use App\Core\DB;

class PasswordRecoveryModel
{
    public function getUserByEmail(string $email): ?array
    {
        $db = DB::connect();
        $stmt = $db->prepare("SELECT id, email, status FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function storeRecoveryToken(int $userId, string $tokenHash): bool
    {
        $db = DB::connect();
        
        // Invalidate previous tokens (soft delete or hard delete?)
        // Hard delete is fine for this use case
        $stmt = $db->prepare("DELETE FROM password_resets WHERE user_id = ?");
        $stmt->execute([$userId]);

        // Insert new token
        $stmt = $db->prepare("INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))");
        return $stmt->execute([$userId, $tokenHash]);
    }

    public function getActiveToken(int $userId): ?array
    {
        $db = DB::connect();
        $stmt = $db->prepare("SELECT token_hash FROM password_resets WHERE user_id = ? AND expires_at > NOW() AND used_at IS NULL LIMIT 1");
        $stmt->execute([$userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function markTokenAsUsed(int $userId): void
    {
        $db = DB::connect();
        $stmt = $db->prepare("UPDATE password_resets SET used_at = NOW() WHERE user_id = ?");
        $stmt->execute([$userId]);
    }

    public function updateUserPassword(int $userId, string $hashedPassword): bool
    {
        $db = DB::connect();
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hashedPassword, $userId]);
    }
}
