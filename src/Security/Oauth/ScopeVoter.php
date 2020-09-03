<?php
declare(strict_types=1);

namespace App\Security\Oauth;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ScopeVoter implements VoterInterface
{
    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;

        foreach ($attributes as $attribute) {
            if (!\is_string($attribute) || 0 !== strpos($attribute, 'SCOPE_')) {
                continue;
            }

            $result = VoterInterface::ACCESS_DENIED;
            foreach ($token->getRoleNames() as $role) {
                if ($attribute === $role) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }
        return $result;
    }
}