<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use App\Shared\Support\UserInitials;
use PHPUnit\Framework\TestCase;

final class UserInitialsTest extends TestCase
{
    public function test_initials_use_first_and_last_name(): void
    {
        $this->assertSame('EU', UserInitials::fromName('Editor User'));
    }

    public function test_initials_ignore_middle_names(): void
    {
        $this->assertSame('EU', UserInitials::fromName('Editor Secondfirstname User'));
        $this->assertSame('EU', UserInitials::fromName('Editor Secondfirstname User Secondlastname'));
    }

    public function test_initials_are_limited_to_two_characters_for_single_token_names(): void
    {
        $this->assertSame('ED', UserInitials::fromName('Editor'));
    }

    public function test_blank_names_fall_back_to_question_mark(): void
    {
        $this->assertSame('?', UserInitials::fromName('   '));
        $this->assertSame('?', UserInitials::fromName(null));
    }
}

