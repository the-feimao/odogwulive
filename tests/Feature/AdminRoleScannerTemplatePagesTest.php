<?php

namespace Tests\Feature;

use App\Models\NotificationTemplate;
use App\Models\Setting;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminRoleScannerTemplatePagesTest extends TestCase
{
    private function activateLicense(): void
    {
        // The existing master layout hides admin content while the license is
        // inactive. Tests run inside a transaction, so this never touches real data.
        Setting::find(1)->update(['license_status' => 1]);
    }

    #[Test]
    public function role_pages_keep_permission_fields_and_admin_protection()
    {
        $this->activateLicense();
        $this->actingAs($this->admin());

        $permission = Permission::firstOrCreate([
            'name' => 'test-permission-'.uniqid(),
            'guard_name' => 'web',
        ]);
        $role = Role::create([
            'name' => 'Event Staff '.uniqid(),
            'guard_name' => 'web',
        ]);
        $role->syncPermissions([$permission]);

        $adminRole = Role::findByName('admin', 'web');

        $this->get('/roles')->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('View Roles')
            ->assertSee($role->name)
            ->assertSee($permission->name)
            ->assertSee('id="report_table"', false)
            ->assertSee('href="'.route('roles.edit', $role->id).'"', false)
            ->assertDontSee('href="'.route('roles.edit', $adminRole->id).'"', false);

        $this->get('/roles/create')->assertOk()
            ->assertSee('action="'.url('roles').'"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="permissions[]"', false)
            ->assertSee('multiple', false)
            ->assertSee('select2');

        $this->get(route('roles.edit', $role))->assertOk()
            ->assertSee('action="'.route('roles.update', [$role->id]).'"', false)
            ->assertSee('readonly', false)
            ->assertSee('name="permissions[]"', false)
            ->assertSee($permission->name)
            ->assertSee('selected', false);

        $newRoleName = 'New Staff '.uniqid();
        $this->post('/roles', [
            'name' => $newRoleName,
            'permissions' => [$permission->id],
        ])->assertRedirect(route('roles.index'));

        $this->assertDatabaseHas('roles', ['name' => $newRoleName]);
        $this->assertTrue(Role::findByName($newRoleName, 'web')->hasPermissionTo($permission));

        $this->put(route('roles.update', $role), [
            'name' => $role->name,
            'permissions' => [],
        ])->assertRedirect(route('roles.index'));

        $this->assertFalse($role->fresh()->hasPermissionTo($permission));
    }

    #[Test]
    public function scanner_pages_keep_block_links_and_form_fields()
    {
        $this->activateLicense();
        $organizer = $this->organizer();
        $scanner = $this->scannerUser($organizer);
        $admin = $this->admin();

        $this->actingAs($admin)->get('/scanner')->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('View Scanner')
            ->assertSee($scanner->first_name.' '.$scanner->last_name)
            ->assertSee($scanner->email)
            ->assertSee('id="report_table"', false)
            ->assertSee('href="'.url('block-scanner/'.$scanner->id).'"', false)
            ->assertSee('title="Block '.$scanner->name.'"', false);

        $this->get('/scanner/create')->assertOk()
            ->assertSee('action="'.url('scanner').'"', false)
            ->assertSee('name="first_name"', false)
            ->assertSee('name="last_name"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="phone"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="status"', false);

        $email = 'new-scanner-'.uniqid().'@example.test';
        $this->post('/scanner', [
            'first_name' => 'New',
            'last_name' => 'Scanner',
            'email' => $email,
            'phone' => '1234567890',
            'password' => 'secret123',
            'status' => 1,
        ])->assertRedirect('/scanner');

        $this->assertDatabaseHas('users', [
            'email' => $email,
            'first_name' => 'New',
            'last_name' => 'Scanner',
            'status' => 1,
        ]);

        $this->get('block-scanner/'.$scanner->id)->assertRedirect('/scanner');
        $this->assertSame('0', (string) $scanner->fresh()->status);

        $this->get('/scanner')
            ->assertSee('title="Unblock '.$scanner->name.'"', false);
    }

    #[Test]
    public function template_pages_keep_summer_note_and_notification_hooks()
    {
        $this->activateLicense();

        $older = NotificationTemplate::create([
            'title' => 'Ticket Ready',
            'subject' => 'Your ticket is ready',
            'mail_content' => '<p>Ticket ready</p>',
            'message_content' => 'Ticket ready',
        ]);
        $latest = NotificationTemplate::create([
            'title' => 'Order Confirmed',
            'subject' => 'Your order is confirmed',
            'mail_content' => '<p>Hello {{user_name}}</p>',
            'message_content' => 'Order confirmed for {{event_name}}',
        ]);

        $this->actingAs($this->admin())->get('/notification-template')->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('Notification Template')
            ->assertSee('Order Confirmed')
            ->assertSee('Ticket Ready')
            ->assertSee('notificationDetail('.$latest->id.');', false)
            ->assertSee('notificationDetail('.$older->id.');', false)
            ->assertSee('action="'.url('notification-template/'.$latest->id).'"', false)
            ->assertSee('id="edit-template-form"', false)
            ->assertSee('name="subject"', false)
            ->assertSee('name="mail_content"', false)
            ->assertSee('name="message_content"', false)
            ->assertSee('textarea_editor')
            ->assertSee('id="message_content"', false)
            ->assertSee('{{user_name}}', false)
            ->assertSee('{{organizer_name}}', false)
            ->assertSee('{{app_name}}', false)
            ->assertSee('{{event_name}}', false)
            ->assertSee('{{date}}', false)
            ->assertSee('{{quantity}}', false)
            ->assertSee('{{password}}', false);

        $this->get('/notification-template/create')->assertOk()
            ->assertSee('action="'.url('notification-template').'"', false)
            ->assertSee('class="event-form', false)
            ->assertSee('name="title"', false)
            ->assertSee('name="subject"', false)
            ->assertSee('name="mail_content"', false)
            ->assertSee('name="message_content"', false)
            ->assertSee('textarea_editor');

        $this->post('/notification-template', [
            'title' => 'Payment Reminder',
            'subject' => 'Payment reminder',
            'mail_content' => '<p>Please complete payment</p>',
            'message_content' => 'Payment reminder',
        ])->assertRedirect(route('notification-template.index'));

        $this->assertDatabaseHas('notification_template', [
            'title' => 'Payment Reminder',
            'subject' => 'Payment reminder',
        ]);
    }
}
