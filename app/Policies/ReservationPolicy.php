<?php

namespace Modules\Resa\Policies;

use App\Policies\GenericSkeletorPolicy;

class ReservationPolicy extends GenericSkeletorPolicy
{
   protected $slug='resa::reservation';
}