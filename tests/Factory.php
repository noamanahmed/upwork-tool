<?php

namespace Tests;

use Tests\Factories\LanguageFactory;
use Tests\Factories\PermissionFactory;
use Tests\Factories\RoleFactory;
use Tests\Factories\UserFactory;

class Factory {

    use UserFactory;
    use RoleFactory;
    use PermissionFactory;
    use LanguageFactory;
}
