<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SubCategory;
use App\Models\Post;
use App\Models\Setting;
use App\Models\SocialMedia;

class BlogController extends BaseController
{
    protected $helpers = ['url','form','CIMail','CIFunctions','text'];
    public function index()
    {
        $data = [
            'pageTitle'=> get_settings()->blog_title,
        ];
        $this->cachePage(600);
        return view('frontend/pages/home', $data);
    }

    public function categoryPosts($category_slug){
        $this->cachePage(600);
        $subcat = new SubCategory();
        $subcategory = $subcat->asObject()->where('slug', $category_slug)->first();
        // $post = new Post();
        $pager = \Config\Services::pager();

        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 6;

        $model = new Post();
        $total = count($model->where('visibility',1)->where('category_id',$subcategory->id)->findAll());

        // Call makeLinks() to make pagination links.
        $pager_links = $pager->makeLinks($page, $perPage, $total);
        $posts = $model->asObject()->where('visibility', 1)->where('category_id',$subcategory->id)->paginate($perPage);

        $data = [
            'pager_links' => $pager_links,
            'pageTitle'=> 'Category: '.$subcategory->name,
            'category' => $subcategory,
            'page' => $page,
            'perPage' => 6,
            'posts' => $posts,
            'total' => $total,
            'pager' => $model->pager,
        ];

        // if(isset($_GET['page'])) {
        //     $page = $_GET['page'];
        // } else {
        //     // set proper default value if it was not set
        //     $page = 1;
        // }

        // $data = [];
        // $data['pageTitle'] = 'Category: '.$subcategory->name;
        // $data['category'] = $subcategory;
        // $data['page'] = isset($_GET['page']) ?? 1;
        // $data['perPage'] = 6;
        // $data['total'] = count($post->where('visibility',1)->where('category_id',$subcategory->id)->findAll());
        // $data['posts'] = $post->asObject()->where('visibility',1)->where('category_id',$subcategory->id)->paginate($data['perPage']);
        // $data['pager'] = $post->where('visibility',1)->where('category_id',$subcategory->id)->pager;
         
        return view('frontend/pages/category_posts', $data); 
        
        // Troubleshoot from here: https://youtu.be/v26PPNarGxM?t=718 
    }

    public function tagPost($tag){
        $this->cachePage(600);
        $post = new Post();
        $data = [];
        $data['pageTitle'] = 'Tag: '.$tag;
        $data['tag'] = $tag;
        $data['page'] = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $data['perPage'] = 6;
        $data['total'] = count($post->where('visibility',1)->like('tags','%'.$tag.'%')->findAll());
        $data['posts'] = $post->asObject()->where('visibility',1)->like('tags','%'.$tag.'%')->orderBy('created_at','desc')->paginate($data['perPage']);
        $data['pager'] = $post->where('visibility',1)->like('tags','%'.$tag.'%')->pager;

        return view('frontend/pages/tag_posts', $data);
    }

    public function searchPost(){
        $request = \Config\Services::request();
        $searchData = $request->getGet();
        $search = isset($searchData) && isset($searchData['q']) ? $searchData['q'] : '';
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = 6;

        // Get Data Object
        $post = new Post();
        // Get Data Count Object
        $post2 = new Post();

        if( $search  == '' ){
            $paginated_data = $post->asObject()->where('visibility',1)->paginate($perPage);
            $total = $post->where('visibility',1)->countAllResults();
            $pager = $post->pager;
        }else{
            $keywords = explode(" ",trim($search));
            $post = $this->getSearchData($post,$keywords);
            $post2 = $this->getSearchData($post2,$keywords);

            $paginated_data = $post->asObject()->where('visibility',1)->paginate($perPage);
            $total = $post2->where('visibility',1)->countAllResults();
            $pager = $post->pager;

            $data = [
                'pageTitle'=> 'Search: '.$search,
                'posts'=>$paginated_data,
                'pager'=>$pager,
                'page'=>$page,
                'perPage'=>$perPage,
                'search'=>$search,
                'total'=>$total
            ];
            return view('frontend/pages/search_posts',$data); 

        }
    }

    public function getSearchData($object,$keywords){
        $object->select('*');
         $object->groupStart();
         foreach($keywords as $keyword){
            $object->orLike('title',$keyword)
                   ->orLike('tags',$keyword);
         }
         return $object->groupEnd();

    }

    public function readPost($slug){
        $this->cachePage(600);
        $post = new Post();
        try{
            $post = $post->asObject()->where('slug',$slug)->first();
            $data = [
                'pageTitle'=>$post->title,
                'post'=>$post
            ];
            return view('frontend/pages/single_post',$data);
        }catch(\Exception $e){
            echo $e->getMessage();
        }
    }

    public function contactUs(){
        $this->cachePage(600);
        $data = [
            'pageTitle'=> 'Contact Us',
            'validation'=> null,
        ];
        return view('frontend/pages/contact_us',$data);
    }

    public function contactUsSend(){
        $request = \Config\Services::request();

        // VALiDATE THE FORM
        $isValid = $this->validate([
            'name' =>[
                'rules'=>'required',
                'errors'=>'Enter your full name',
            ],
            'email' =>[
                'rules'=>'required|valid_email',
                'errors'=>[
                    'required'=> 'Enter a valid email address',
                    'valid_email'=> 'Enter a valid email address',
                ]
            ],
            'subject' =>[
                'rules'=>'required',
                'errors'=>'Enter a subject for this email',
            ],
            'message' =>[
                'rules'=>'required',
                'errors'=>'Enter message content',
            ]

        ]);
        if( !$isValid ){
            $data = [
                'pageTitle'=>'Contact Us',
                'validation'=>$this->validator
            ];
            return view('frontend/pages/contact_us',$data);
        }else{
            // if VAlidation pass :: CREATE EMAIL BODY
            $mail_body = 'Message from: <b>'.$request->getVar('name').'</b></br>';
            $mail_body.= '-------------------------------------------------</br>';
            $mail_body.= ''.$request->getVar('message').'</br>';

            $mailConfig = array(
                'mail_from_email'=> env('EMAIL_FROM_ADDRESS'),
                'mail_from_name'=> $request->getVar('name'),
                'mail_recipient_email'=> get_settings()->blog_email,
                'mail_recipient_name'=> get_settings()->blog_title,
                'mail_subject'=> $request->getVar('subject'),
                'mail_body'=>$mail_body
            );
            if( sendEmail($mailConfig) ){
                return redirect()->route('contact-us')->with('success','Your message has been sent!.');
            }else{
                return redirect()->route('contact-us')->with('error','Ooops!, something went wrong.');
            }
        }
    }
}