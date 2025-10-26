<?php
declare(strict_types=1);

use App\CustomController;
use Domain\AppYorewebCom\Models\User;
use Domain\AppYorewebCom\Models\Text;
use Domain\AppYorewebCom\Models\SiteData;

class Controller extends CustomController
{
    public function __construct()
    {
        // Define page metadata for this controller route (replaces the need for about.json)
        $this->domain = 'app.yoreweb.com';
        $this->site   = 'about';
        $this->page   = 'about';
        $this->title  = 'App';
        $this->theme  = 'app';
        $this->desc   = 'This page should be coming from the Controller and not a view';
        $this->security = false;
        $this->public   = true;

        parent::__construct();
    }

    // Default action: responds to /about and renders with data from domain Models
    public function web_index(...$args)
    {
        // 1) Basic query & pluck
        try {
            $activeUsers = User::where('is_active', '=', 1)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            $userEmails = method_exists($activeUsers, 'pluck') ? $activeUsers->pluck('email') : [];
        } catch (\Throwable $e) {
            $activeUsers = new class {
                public function all(){ return []; }
            };
            $userEmails = [];
        }

        // 2) Fetch a Text page by slug
        $aboutTitle = 'About';
        $aboutBody  = 'Welcome to our site.';
        try {
            $aboutPage = Text::where('slug', '=', 'about')->first();
            if ($aboutPage) {
                $aboutTitle = $aboutPage->getAttribute('title') ?? $aboutTitle;
                $aboutBody  = $aboutPage->getAttribute('body')  ?? $aboutBody;
            }
        } catch (\Throwable $e) {}

        // 3) Site settings read/write
        try {
            $currentTheme = SiteData::get('theme', 'default');
            if (!$currentTheme) {
                SiteData::set('theme', 'default');
                $currentTheme = 'default';
            }
        } catch (\Throwable $e) {
            $currentTheme = 'default';
        }

        // 4) Demonstrate create/update
        try {
            if (!User::where('email','=','demo@example.com')->exists()) {
                $demo = User::create([
                    'name'  => 'Demo User',
                    'email' => 'demo@example.com',
                    'is_active' => 1,
                    'created_at' => date('c'),
                    'updated_at' => date('c'),
                ]);
                if (is_object($demo) && method_exists($demo, 'setAttribute')) {
                    $demo->setAttribute('name', 'Demo User Jr.');
                    $demo->save();
                }
            }
        } catch (\Throwable $e) {}

        // 5) Render simple HTML since custom controllers return strings
        $emailsList = is_array($userEmails) ? $userEmails : (method_exists($userEmails, 'all') ? $userEmails->all() : []);
        $emailsHtml = '';
        foreach ($emailsList as $em) {
            $emailsHtml .= '<li>' . htmlspecialchars((string)$em) . '</li>';
        }

        $count = is_object($activeUsers) && method_exists($activeUsers, 'all') ? count($activeUsers->all()) : (is_array($activeUsers) ? count($activeUsers) : 0);

        return '<div class="container">'
            . '<h1>' . htmlspecialchars($aboutTitle) . '</h1>'
            . '<p>' . htmlspecialchars($aboutBody) . '</p>'
            . '<p><strong>Theme:</strong> ' . htmlspecialchars($currentTheme) . '</p>'
            . '<p><strong>Active users (up to 5):</strong> ' . $count . '</p>'
            . '<ul>' . $emailsHtml . '</ul>'
            . '</div>';
    }

    // Example web method: responds to /about/about and renders into the theme layout
    public function web_about(...$args)
    {
        $extra = '';
        if (!empty($args)) {
            $extra = '<p>Args: ' . htmlspecialchars(implode(', ', $args)) . '</p>';
        }
        return '<div class="container"><h1>About (Custom Controller)</h1><p>This is coming from /pages/_domains/app.yoreweb.com/about/Controller.php::web_about()</p>' . $extra . '</div>';
    }
}
