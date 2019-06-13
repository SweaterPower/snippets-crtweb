<?php

namespace App\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use App\Entity\Snippet;
use App\Entity\User;
use App\Entity\UserRole;

/**
 * Воутер для контроля доступа к редактированию и просмотру сниппетов
 */
class SnippetVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';
    
    private $decisionManager;
    
    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::VIEW, self::EDIT))) {
            return false;
        }

        if (!$subject instanceof Snippet) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        $snippet = $subject;
        
        if (!$user instanceof User) {
            return !$snippet->getIsPrivate();
        }

        if (in_array(UserRole::USER_ROLE_ADMIN, $token->getRoles())) {
            return true;
        }
        
        switch ($attribute) {
            case self::VIEW:
                return $this->canView($snippet, $user);
            case self::EDIT:
                return $this->canEdit($snippet, $user);
        }

        throw new \LogicException('Snippet voter error.');
    }

    private function canView(Snippet $snippet, User $user)
    {
        if ($this->canEdit($snippet, $user)) {
            return true;
        }
        
        return !$snippet->getIsPrivate();
    }

    private function canEdit(Snippet $snippet, User $user)
    {
        return $user === $snippet->getOwner();
    }
}
