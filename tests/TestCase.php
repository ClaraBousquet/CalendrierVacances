<?php

namespace App\Tests;

use App\Tests\Framework\Traits\UserLogin;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class TestCase extends WebTestCase
{
    use UserLogin;
}
