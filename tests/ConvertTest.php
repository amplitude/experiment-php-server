<?php

namespace AmplitudeExperiment\Test\Local;

use PHPUnit\Framework\TestCase;
use AmplitudeExperiment\User;

class ConvertTest extends TestCase
{
    public function testConvertUserToContext_UndefinedGroups()
    {
        $user = User::builder()->build();
        $context = $user->toEvaluationContext();
        $this->assertEquals(['user' => []], $context);
    }

    public function testConvertUserToContext_EmptyGroups()
    {
        $user = User::builder()->groups([])->build();
        $context = $user->toEvaluationContext();
        $this->assertEquals(['user' => []], $context);
    }

    public function testConvertUserToContext_RemoveGroupsAndGroupProperties()
    {
        $user = User::builder()
            ->userId('user_id')
            ->groups([])
            ->groupProperties([])
            ->build();
        $context = $user->toEvaluationContext();
        $this->assertEquals(['user' => ['user_id' => 'user_id']], $context);
    }

    public function testConvertUserToContext_UserGroupsWithUndefinedGroupProperties()
    {
        $user = User::builder()
            ->userId('user_id')
            ->groups(['gt1' => ['gn1']])
            ->build();
        $context = $user->toEvaluationContext();
        $this->assertEquals([
            'user' => ['user_id' => 'user_id'],
            'groups' => ['gt1' => ['group_name' => 'gn1']],
        ], $context);
    }

    public function testConvertUserToContext_UserGroupsWithEmptyGroupProperties()
    {
        $user = User::builder()
            ->userId('user_id')
            ->groups(['gt1' => ['gn1']])
            ->groupProperties([])
            ->build();
        $context = $user->toEvaluationContext();
        $this->assertEquals([
            'user' => ['user_id' => 'user_id'],
            'groups' => ['gt1' => ['group_name' => 'gn1']],
        ], $context);
    }

    public function testConvertUserToContext_UserGroupsWithEmptyGroupTypeObject()
    {
        $user = User::builder()
            ->userId('user_id')
            ->groups(['gt1' => ['gn1']])
            ->groupProperties(['gt1' => []])
            ->build();
        $context = $user->toEvaluationContext();
        $this->assertEquals([
            'user' => ['user_id' => 'user_id'],
            'groups' => ['gt1' => ['group_name' => 'gn1']],
        ], $context);
    }

    public function testConvertUserToContext_UserGroupsWithEmptyGroupNameObject()
    {
        $user = User::builder()
            ->userId('user_id')
            ->groups(['gt1' => ['gn1']])
            ->groupProperties(['gt1' => ['gn1' => []]])
            ->build();
        $context = $user->toEvaluationContext();
        $this->assertEquals([
            'user' => ['user_id' => 'user_id'],
            'groups' => ['gt1' => ['group_name' => 'gn1']],
        ], $context);
    }

    public function testConvertUserToContext_UserGroupWithGroupProperties()
    {
        $user = User::builder()
            ->userId('user_id')
            ->groups(['gt1' => ['gn1']])
            ->groupProperties(['gt1' => ['gn1' => ['gp1' => 'gp1']]])
            ->build();
        $context = $user->toEvaluationContext();
        $this->assertEquals([
            'user' => ['user_id' => 'user_id'],
            'groups' => ['gt1' => [
                'group_name' => 'gn1',
                'group_properties' => ['gp1' => 'gp1']
            ]]], $context);
    }

    public function testConvertUserToContext_UserGroupWithMultipleGroupNames_TakesFirst()
    {
        $user = User::builder()
            ->userId('user_id')
            ->groups(['gt1' => ['gn1', 'gn2']])
            ->groupProperties(['gt1' => [
                'gn1' => ['gp1' => 'gp1'],
                'gn2' => ['gp2' => 'gp2'],
            ]])
            ->build();
        $context = $user->toEvaluationContext();
        $this->assertEquals([
            'user' => ['user_id' => 'user_id'],
            'groups' => ['gt1' => [
                'group_name' => 'gn1',
                'group_properties' => ['gp1' => 'gp1'],
            ]
            ]], $context);
    }
}
