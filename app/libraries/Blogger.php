<?

namespace app\libraries;

use app\models\Blogger\BlogsModel;
use app\models\Blogger\PagesModel;
use app\models\Blogger\PostsModel;
use app\models\Youtube\ChannelsModel;
use app\models\Youtube\PlaylistsModel;
use app\models\Youtube\SubscriptionsModel;
use app\models\Youtube\VideosModel;
use app\models\MigratedDataModel;

class Blogger
{
	public static $name              = 'Blogger';
	public static $limit             = 50;

	public static $kind = array(
		'blog' => 'blogger#blog',
		'post' => 'blogger#post',
		'page' => 'blogger#page',
	);

	public static $endpoints = array(
		'blogs' => 'https://www.googleapis.com/blogger/v3/users/self/blogs',
		'posts' => 'https://www.googleapis.com/blogger/v3/blogs/%s/posts',
		'pages' => 'https://www.googleapis.com/blogger/v3/blogs/%s/pages',
	);

	public static function sync($source, $destination, $syncTaskId = 0)
	{
	}

	public static function backup($user, $taskId = 0, $syncTaskId = 0)
	{
		// Stats
		$stats = array(
			'blogs'     => 0,
			'posts'     => 0,
			'pages'     => 0
		);

		// Channels
		$blogs = \Rest::get(
			static::$endpoints['blogs'],
			array( 'view' => 'ADMIN'),
			$user
		);

		if($blogs && $blogs['items']) {
			foreach($blogs['items'] as $blog) {

				// Save blog
				$newBlog = BlogsModel::create();
				$newBlog->user_id      = $user['username']['id'];
				$newBlog->task_id      = $taskId;
				$newBlog->sync_task_id = $syncTaskId;
				$newBlog->kind         = static::$kind['blog'];
				$newBlog->blog_id      = $blog['id'];
				$newBlog->name         = $blog['name'];
				$newBlog->description  = $blog['description'];
				$newBlog->url          = $blog['url'];
				$newBlog->created_at   = date(DATE_TIME, strtotime($blog['published']));
				$newBlog->status       = BlogsModel::STATUS_ACTIVE;
				$newBlog->save();

				$stats['blogs']++;

				$newPosts = array();

				do {
					$payload = array(
						'view'       => 'ADMIN',
//						'maxResults' => static::$limit,
					);

					if(isset($posts['nextPageToken'])) {
						$payload['pageToken'] = $posts['nextPageToken'];
					}

					$posts = array();
					$posts = \Rest::get(
						sprintf(static::$endpoints['posts'], $blog['id']),
						$payload,
						$user
					);

					if(isset($posts['result']['error'])) {
						d($posts);
					}

					if($posts['items']) {
						foreach($posts['items'] as $post) {

							$formatedData = array(
								'user_id'         => $user['username']['id'],
								'task_id'         => $taskId,
								'sync_task_id'    => $syncTaskId,
								'blog_id'         => $newBlog->id,
								'blogger_blog_id' => $blog['id'],
								'kind'            => static::$kind['post'],
								'post_id'         => $post['id'],
								'post_status'     => $post['status'],
								'title'           => $post['title'],
								'content'         => $post['content'],
								'created_at'      => date(DATE_TIME,strtotime($post['published'])),
								'author_id'       => $post['author']['id'],
								'author_name'     => $post['author']['displayName'],
								'author_url'      => $post['author']['url'],
								'author_avatar'   => $post['author']['image']['url'],
								'status'          => PostsModel::STATUS_ACTIVE
							);

							$newPosts[] = $formatedData;

							$stats['posts']++;
						}
					}

				} while(isset($posts['nextPageToken']) && $posts['nextPageToken']);

				if($newPosts) {
					PostsModel::insertBatch($newPosts);
				}

				// Pages
				$pages = \Rest::get(
					sprintf(static::$endpoints['pages'], $blog['id']),
					array( 'view' => 'ADMIN'),
					$user
				);

				if($pages && $pages['items']) {
					foreach($pages['items'] as $page) {

						$newPage = PagesModel::create();
						$newPage->user_id         = $user['username']['id'];
						$newPage->task_id         = $taskId;
						$newPage->sync_task_id    = $syncTaskId;
						$newPage->blog_id         = $newBlog->id;
						$newPage->blogger_blog_id = $blog['id'];
						$newPage->kind            = static::$kind['post'];
						$newPage->page_id         = $page['id'];
						$newPage->page_status     = $page['status'];
						$newPage->title           = $page['title'];
						$newPage->content         = $page['content'];
						$newPage->created_at      = strtotime(DATE_TIME, strtotime($page['published']));
						$newPage->author_id       = $page['author']['id'];
						$newPage->author_name     = $page['author']['displayName'];
						$newPage->author_url      = $page['author']['url'];
						$newPage->author_avatar   = $page['author']['image']['url'];
						$newPage->status          = PostsModel::STATUS_ACTIVE;
						$newPage->save();

						$stats['pages']++;
					}
				}
			}
		}

		return $stats;
	}

	public static function export()
	{
	}
}