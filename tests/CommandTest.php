<?php

namespace Kerigard\LaravelData\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Kerigard\LaravelData\Data;
use Kerigard\LaravelData\DataManager;
use Kerigard\LaravelData\Tests\Models\Role;

class CommandTest extends TestCase
{
    public function test_command()
    {
        Carbon::setTestNow($now = Carbon::now()->toDateTimeString());

        DataManager::model(Role::class, fn () => Data::make([
            ['id' => 1, 'name' => 'admin'],
            ['id' => 2, 'name' => 'user'],
        ]));

        DataManager::table('role_user', fn () => Data::make([
            ['role_id' => 1, 'user_id' => 1],
            ['role_id' => 3, 'user_id' => 2],
        ]));

        DataManager::table([
            'table' => 'permissions',
            'primaryKey' => 'id',
            'timestamps' => true,
        ], fn () => Data::make([
            ['id' => 5, 'name' => 'edit-posts'],
        ]));

        Model::unguarded(fn () => Role::query()->create(['id' => 1, 'name' => 'admin']));

        DB::enableQueryLog();

        $this->artisan('db:data')->assertSuccessful();

        $this->assertCount(16, $log = DB::getQueryLog());
        $this->assertEquals('select * from "countries" where ("code" = ?) limit 1', $log[0]['query']);
        $this->assertEquals(['USA'], $log[0]['bindings']);
        $this->assertEquals('insert into "countries" ("code", "name", "slug", "updated_at", "created_at") values (?, ?, ?, ?, ?)', $log[1]['query']);
        $this->assertEquals(['USA', 'United States', 'united-states', $now, $now], $log[1]['bindings']);
        $this->assertEquals('select * from "countries" where ("code" = ?) limit 1', $log[2]['query']);
        $this->assertEquals(['RUS'], $log[2]['bindings']);
        $this->assertEquals('insert into "countries" ("code", "name", "slug", "updated_at", "created_at") values (?, ?, ?, ?, ?)', $log[3]['query']);
        $this->assertEquals(['RUS', 'Russia', 'russia', $now, $now], $log[3]['bindings']);
        $this->assertEquals('delete from "roles" where "id" not in (?, ?)', $log[4]['query']);
        $this->assertEquals([1, 2], $log[4]['bindings']);
        $this->assertEquals('select * from "roles" where ("id" = ?) limit 1', $log[5]['query']);
        $this->assertEquals([1], $log[5]['bindings']);
        $this->assertEquals('select * from "roles" where ("id" = ?) limit 1', $log[6]['query']);
        $this->assertEquals([2], $log[6]['bindings']);
        $this->assertEquals('insert into "roles" ("id", "name", "updated_at", "created_at") values (?, ?, ?, ?)', $log[7]['query']);
        $this->assertEquals([2, 'user', $now, $now], $log[7]['bindings']);
        $this->assertEquals('delete from "role_user" where not (("role_id" = ? and "user_id" = ?) or ("role_id" = ? and "user_id" = ?))', $log[8]['query']);
        $this->assertEquals([1, 1, 3, 2], $log[8]['bindings']);
        $this->assertEquals('select * from "role_user" where ("role_id" = ? and "user_id" = ?) limit 1', $log[9]['query']);
        $this->assertEquals([1, 1], $log[9]['bindings']);
        $this->assertEquals('insert into "role_user" ("role_id", "user_id") values (?, ?)', $log[10]['query']);
        $this->assertEquals([1, 1], $log[10]['bindings']);
        $this->assertEquals('select * from "role_user" where ("role_id" = ? and "user_id" = ?) limit 1', $log[11]['query']);
        $this->assertEquals([3, 2], $log[11]['bindings']);
        $this->assertEquals('insert into "role_user" ("role_id", "user_id") values (?, ?)', $log[12]['query']);
        $this->assertEquals([3, 2], $log[12]['bindings']);
        $this->assertEquals('delete from "permissions" where "id" not in (?)', $log[13]['query']);
        $this->assertEquals([5], $log[13]['bindings']);
        $this->assertEquals('select * from "permissions" where ("id" = ?) limit 1', $log[14]['query']);
        $this->assertEquals([5], $log[14]['bindings']);
        $this->assertEquals('insert into "permissions" ("id", "name", "updated_at", "created_at") values (?, ?, ?, ?)', $log[15]['query']);
        $this->assertEquals([5, 'edit-posts', $now, $now], $log[15]['bindings']);
    }
}
