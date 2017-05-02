<?php

namespace AppBundle\Managers;

use Ramsey\Uuid\Uuid as Uuid;

class SlugManager {
	public static function generateSlug()
	{
		return Uuid::uuid1()->toString();
	}
}
