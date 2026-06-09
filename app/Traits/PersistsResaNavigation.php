<?php

namespace Modules\Resa\Traits;

trait PersistsResaNavigation
{
    private const SESSION_LIEU  = 'resa_nav_lieu';
    private const SESSION_UNITE = 'resa_nav_unite';

    protected function restoreNavigationFromSession(): void
    {
        $lieuId  = session(self::SESSION_LIEU);
        $uniteId = session(self::SESSION_UNITE);

        if ($lieuId)  $this->lieuActive  = (int) $lieuId;
        if ($uniteId) $this->uniteActive = (int) $uniteId;
    }

    protected function saveNavigationToSession(): void
    {
        session([
            self::SESSION_LIEU  => $this->lieuActive,
            self::SESSION_UNITE => $this->uniteActive,
        ]);
    }

    protected function clearNavigationSession(): void
    {
        session()->forget([self::SESSION_LIEU, self::SESSION_UNITE]);
    }
}
