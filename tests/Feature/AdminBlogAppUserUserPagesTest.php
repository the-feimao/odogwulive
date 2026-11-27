<?php

namespace Tests\Feature;

use App\Models\AppUser;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminBlogAppUserUserPagesTest extends TestCase
{
    private function activateLicense(): void
    {
        // The existing master layout hides admin content while the license is
        // inactive. Tests run inside a transaction, so this never touches real data.
        Setting::find(1)->update(['license_status' => 1]);
    }

    private function userWithoutRole(): User
    {
        return User::create([
            'name' => 'No Access',
            'first_name' => 'No',
            'last_name' => 'Access',
            'email' => 'no-access-'.uniqid().'@example.test',
            'password' => bcrypt('secret123'),
            'status' => 1,
            'language' => 'English',
        ]);
    }

    #[Test]
    public function blog_pages_keep_image_hooks_and_crud_contracts()
    {
        $this->activateLicense();
        $admin = $this->admin();
        $category = $this->category();
        $blog = Blog::create([
            'title' => 'Launch Announcement',
            'description' => 'The launch is almost here.',
            'category_id' => $category->id,
            'tags' => 'launch,news',
            'image' => 'launch.jpg',
            'status' => 1,
        ]);

        $this->actingAs($admin)->get('/blog')->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('All Blogs')
            ->assertSee('Launch Announcement')
            ->assertSee('id="report_table"', false)
            ->assertSee("deleteData('blog','".$blog->id."');", false)
            ->assertSee('href="'.route('blog.edit', $blog->id).'"', false);

        $this->get('/blog/create')->assertOk()
            ->assertSee('action="'.url('blog').'"', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('id="image-preview"', false)
            ->assertSee('id="image-upload"', false)
            ->assertSee('id="image-label"', false)
            ->assertSee('name="title"', false)
            ->assertSee('name="category_id"', false)
            ->assertSee('name="status"', false)
            ->assertSee('name="tags"', false)
            ->assertSee('inputtags')
            ->assertSee('name="description"', false)
            ->assertSee('<trix-editor', false)
            ->assertSee('class="ds-rich-text trix-content"', false)
            ->assertSee('select2');

        $this->get(route('blog.edit', $blog))->assertOk()
            ->assertSee('action="'.route('blog.update', [$blog->id]).'"', false)
            ->assertSee('name="_method"', false)
            ->assertSee('value="PUT"', false)
            ->assertSee('background-image: url(')
            ->assertSee('Launch Announcement')
            ->assertSee('launch,news')
            ->assertSee('<trix-editor', false);

        $before = glob(public_path('images/upload/*')) ?: [];

        $this->post('/blog', [
            'title' => 'Community Update',
            'description' => 'A fresh update for the community.',
            'category_id' => $category->id,
            'status' => 1,
            'image' => UploadedFile::fake()->create('community.jpg', 1, 'image/jpeg'),
        ])->assertRedirect(route('blog.index'));

        $this->assertDatabaseHas('blog', [
            'title' => 'Community Update',
            'category_id' => $category->id,
        ]);

        foreach (array_diff((glob(public_path('images/upload/*')) ?: []), $before) as $file) {
            @unlink($file);
        }

        $this->put(route('blog.update', $blog), [
            'title' => 'Updated Announcement',
            'description' => 'Updated details.',
            'category_id' => $category->id,
            'status' => 0,
        ])->assertRedirect(route('blog.index'));

        $this->assertDatabaseHas('blog', [
            'id' => $blog->id,
            'title' => 'Updated Announcement',
            'status' => 0,
        ]);
    }

    #[Test]
    public function attendee_pages_keep_block_detail_and_upload_contracts()
    {
        $this->activateLicense();
        $organizer = $this->organizer();
        $admin = $this->admin();
        $customer = $this->customer([
            'name' => 'Ada Attendee',
            'following' => (string) $organizer->id,
        ]);
        $event = $this->event($organizer, ['name' => 'Attendee Event']);
        $ticket = $this->ticket($event);
        $order = $this->order($event, $ticket, $customer, $organizer, [
            'order_id' => '#ATT-100',
            'order_status' => 'Pending',
        ]);

        $this->actingAs($admin)->get('/app-user')->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('View Attendees')
            ->assertSee('Ada Attendee')
            ->assertSee($customer->email)
            ->assertSee('id="report_table"', false)
            ->assertSee('href="'.url('view-user/'.$customer->id).'"', false)
            ->assertSee('href="'.url('block-user/'.$customer->id).'"', false)
            ->assertSee('title="Block Ada Attendee"', false);

        $this->get('/app-user/create')->assertOk()
            ->assertSee('action="'.url('app-user').'"', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('id="image-preview"', false)
            ->assertSee('id="image-upload"', false)
            ->assertSee('id="image-label"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="status"', false)
            ->assertSee('select2');

        $email = 'attendee-'.uniqid().'@example.test';
        $this->post('/app-user', [
            'name' => 'New Attendee',
            'email' => $email,
            'password' => 'secret123',
            'status' => 1,
        ])->assertRedirect(route('app-user.index'));

        $this->assertDatabaseHas('app_user', [
            'email' => $email,
            'name' => 'New Attendee',
            'image' => 'defaultuser.png',
            'provider' => 'LOCAL',
        ]);

        $this->get('view-user/'.$customer->id)->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('Ada Attendee')
            ->assertSee('id="myTab"', false)
            ->assertSee('id="upcoming-tab"', false)
            ->assertSee('id="past-tab"', false)
            ->assertSee('id="upcoming"', false)
            ->assertSee('id="past"', false)
            ->assertSee('data-toggle="tab"', false)
            ->assertSee('#ATT-100')
            ->assertSee('href="'.url('order-invoice/'.$order->id).'"', false)
            ->assertSee('Attendee Event')
            ->assertSee($organizer->email);

        $this->get('block-user/'.$customer->id)->assertRedirect(route('app-user.index'));
        $this->assertSame('0', (string) $customer->fresh()->status);

        $this->get('/app-user')->assertSee('title="Unblock Ada Attendee"', false);
    }

    #[Test]
    public function organizers_get_a_read_only_attendee_list_scoped_to_their_orders()
    {
        $this->activateLicense();
        $organizer = $this->organizer();
        $otherOrganizer = $this->organizer();
        $admin = $this->admin();
        $scanner = $this->scannerUser($organizer);

        $customer = $this->customer(['name' => 'Organizer Customer']);
        $event = $this->event($organizer);
        $ticket = $this->ticket($event);
        $this->order($event, $ticket, $customer, $organizer);

        $outsider = $this->customer(['name' => 'Other Organizer Customer']);
        $otherEvent = $this->event($otherOrganizer);
        $otherTicket = $this->ticket($otherEvent);
        $this->order($otherEvent, $otherTicket, $outsider, $otherOrganizer);

        $this->actingAs($organizer)->get('/app-user')->assertOk()
            ->assertSee('Review attendees who have placed orders for your events.')
            ->assertSee('Organizer Customer')
            ->assertDontSee('Other Organizer Customer')
            ->assertDontSee('href="'.url('app-user/create').'"', false)
            ->assertDontSee('href="'.url('block-user/'.$customer->id).'"', false)
            ->assertSee('href="'.url('view-user/'.$customer->id).'"', false)
            ->assertViewHas('users', function ($users) use ($customer, $outsider) {
                return $users->contains('id', $customer->id) && !$users->contains('id', $outsider->id);
            });

        $this->get('/app-user/create')->assertForbidden();
        $this->post('/app-user', [
            'name' => 'Should Not Exist',
            'email' => 'should-not-exist-'.uniqid().'@example.test',
            'password' => 'secret123',
            'status' => 1,
        ])->assertForbidden();
        $this->get('block-user/'.$customer->id)->assertForbidden();
        $this->get('view-user/'.$outsider->id)->assertForbidden();

        $this->actingAs($scanner)->get('/app-user')->assertForbidden();

        $this->actingAs($admin)->get('/app-user')->assertOk()
            ->assertSee('Organizer Customer')
            ->assertSee('Other Organizer Customer')
            ->assertSee('href="'.url('block-user/'.$customer->id).'"', false);
    }

    #[Test]
    public function user_pages_keep_role_hooks_and_admin_protection()
    {
        $this->activateLicense();
        $admin = $this->admin();
        $organizer = $this->organizer();
        $scanner = User::create([
            'name' => 'Role Scanner',
            'first_name' => 'Role',
            'last_name' => 'Scanner',
            'email' => 'role-scanner-'.uniqid().'@example.test',
            'password' => bcrypt('secret123'),
            'phone' => '08012345678',
            'status' => 1,
            'language' => 'English',
        ]);
        $scanner->assignRole(Role::findByName('scanner', 'web'));

        $adminRole = Role::findByName('admin', 'web');

        $this->actingAs($admin)->get('/users')->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('View Users')
            ->assertSee('Role Scanner')
            ->assertSee('08012345678')
            ->assertSee('scanner')
            ->assertSee('id="report_table"', false)
            ->assertSee('href="'.route('users.edit', $scanner->id).'"', false)
            ->assertDontSee('href="'.route('users.edit', $admin->id).'"', false);

        $this->get('/users/create')->assertOk()
            ->assertSee('action="'.url('users').'"', false)
            ->assertSee('name="first_name"', false)
            ->assertSee('name="last_name"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="phone"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="roles[]"', false)
            ->assertSee('id="role"', false)
            ->assertSee('multiple="multiple"', false)
            ->assertSee('name="organization"', false)
            ->assertSee('id="org"', false)
            ->assertSee('class="display-none"', false)
            ->assertSee('select2');

        $this->get(route('users.edit', $scanner))->assertOk()
            ->assertSee('action="'.route('users.update', [$scanner->id]).'"', false)
            ->assertSee('name="_method"', false)
            ->assertSee('value="PUT"', false)
            ->assertSee('name="email"', false)
            ->assertSee('readonly', false)
            ->assertSee('name="roles[]"', false)
            ->assertSee('id="role"', false)
            ->assertSee('id="org"', false)
            ->assertSee('class="display-none"', false)
            ->assertSee('selected', false);

        $newEmail = 'new-user-'.uniqid().'@example.test';
        $this->post('/users', [
            'first_name' => 'New',
            'last_name' => 'User',
            'email' => $newEmail,
            'phone' => '08098765432',
            'password' => 'secret123',
            'roles' => [(string) $organizer->roles()->first()->id],
            'organization' => $organizer->id,
        ])->assertRedirect(route('users.index'));

        $createdUser = User::where('email', $newEmail)->firstOrFail();
        $this->assertDatabaseHas('users', [
            'email' => $newEmail,
            'first_name' => 'New',
            'org_id' => $organizer->id,
        ]);
        $this->assertTrue($createdUser->hasRole('organization'));

        $this->put(route('users.update', $scanner), [
            'first_name' => 'Updated',
            'last_name' => 'Scanner',
            'email' => $scanner->email,
            'phone' => '08011111111',
            'roles' => [(string) Role::findByName('scanner', 'web')->id],
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $scanner->id,
            'first_name' => 'Updated',
            'phone' => '08011111111',
        ]);
        $this->assertTrue($scanner->fresh()->hasRole('scanner'));

        $this->get(route('users.edit', $admin))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('status', __('You can not edit admin.'));

        $outsider = $this->userWithoutRole();
        $customer = $this->customer();

        $this->actingAs($outsider)->get('/blog')->assertForbidden();
        $this->get('/app-user')->assertForbidden();
        $this->get('/users')->assertForbidden();
        $this->get('view-user/'.$customer->id)->assertForbidden();

        $this->assertNotNull($adminRole);
    }
}
