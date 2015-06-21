<?php

namespace MonkBlog\Http\Controllers;

use MonkBlog\Models\Post;
use MonkBlog\Models\Page;

class HomeController extends BaseController
{

    public function getHome()
    {
        $recentPosts = Post::where( 'is_published', '=', true )->orderBy( 'published_at', 'desc' )->take( 4 )->get();

        $featuredPost = $recentPosts->shift();

        $more = false;

        if( Post::where( 'is_published', '=', true )->count() > 5 ) {
            $more = true;
        }

        $viewData = [
            'pageTitle' => 'Home',
            'featuredPost' => $featuredPost,
            'recentPosts' => $recentPosts,
            'more' => $more,
        ];

        return view( 'home', $viewData );
    }

    public function getAdminHome()
    {
        $totalPosts = Post::count();
        $totalPages = Page::count();

        $viewData = [
            'pageTitle' => 'Dashboard',
            'totalPosts' => $totalPosts,
            'totalPages' => $totalPages,
        ];

        return view( 'admin.home', $viewData );
    }

}
