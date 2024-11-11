<?php

namespace Tests\Unit;

use App\Enums\Direction;
use App\Enums\RuleType;
use App\Models\Rule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_rule_is_applied_to_data(): void
    {
        $user = User::factory()->create();
        $rule = Rule::create([
            'type' => RuleType::CATEGORY,
            'user_id' => $user->id,
            'description_lookup' => 'text',
            'direction_lookup' => Direction::EXPENSE
        ]);
        $this->assertTrue($rule->checkRuleIsAppliedToData([
            'description' => 'long text',
            'direction' => Direction::EXPENSE
        ]));
        $this->assertFalse($rule->checkRuleIsAppliedToData([
            'description' => 'long text',
            'direction' => Direction::INCOME
        ]));
        $rule = Rule::create([
            'type' => RuleType::CATEGORY,
            'user_id' => $user->id,
            'description_lookup' => 'text',
        ]);
        $this->assertTrue($rule->checkRuleIsAppliedToData([
            'description' => 'long text',
            'direction' => Direction::INCOME
        ]));
    }

    public function test_exclude_collection_filter(): void
    {
        $user = User::factory()->create();
        $rule = Rule::create([
            'type' => RuleType::CATEGORY,
            'user_id' => $user->id,
            'description_lookup' => 'text',
            'direction_lookup' => Direction::EXPENSE
        ]);
        $this->assertTrue($rule->checkRuleIsAppliedToData([
            'description' => 'long text',
            'direction' => Direction::EXPENSE
        ]));
        $this->assertFalse($rule->checkRuleIsAppliedToData([
            'description' => 'long text',
            'direction' => Direction::INCOME
        ]));
        $rule = Rule::create([
            'type' => RuleType::CATEGORY,
            'user_id' => $user->id,
            'description_lookup' => 'text',
        ]);
        $this->assertTrue($rule->checkRuleIsAppliedToData([
            'description' => 'long text',
            'direction' => Direction::INCOME
        ]));
    }
}
