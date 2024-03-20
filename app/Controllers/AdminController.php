<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\CIAuth;
use App\Models\User;
use App\Libraries\Hash;
use App\Models\Setting;
use App\Models\SocialMedia;
use App\Models\Category;
use CodeIgniter\CLI\Console;
use SSP;
use \Mberecall\CI_Slugify\SlugService;
use App\Models\SubCategory;
use App\Models\Post;

class AdminController extends BaseController
{
    protected $helpers = ['url','form','CIMail','CIFunctions'];
    protected $db;

    public function __construct()
    {
        require_once APPPATH.'ThirdParty/ssp.php';
        $this->db = db_connect();
    }
    
    public function index()
    {
        $data = [
            'pageTitle'=>'Dashboard',

        ];
        return view('backend/pages/home', $data);
    }

    public function logoutHandler(){
        CIAuth::forget();
        return redirect()->route('admin.login.form')->with('fail','You are logged out!');
    }

    public function profile(){
        $data = array(
            'pageTitle' => 'Profile',
        );
        return view('backend/pages/profile', $data);

    }

    public function updatePersonalDetails(){
        $request = \Config\Services::request();
        $validation = \Config\Services::validation();
        $user_id = CIAuth::id();

        if( $request->isAjax() ){
            $this->validate([
                'name'=>[
                    'rules'=>'required',
                    'errors'=>[
                        'required'=>'Full name is required'
                    ]
                ],
                'username'=>[
                    'rules'=>'required|min_length[4]|is_unique[users.username,id,'.$user_id.']',
                    'errors'=>[
                        'required'=>'Username is required',
                        'min_length'=>'Username must be a minmum of 4 characters',
                        'is_unique'=>'Username is already taken'
                    ]
                ]
            ]);
 
            if( $validation->run() == FALSE){
                $errors = $validation->getErrors();
                return json_encode(['status'=>0,'error'=>$errors]);
            }else{
                $user = new User();
                $update = $user->where('id',$user_id)
                               ->set([
                                    'name'=>$request->getVar('name'),
                                    'username'=>$request->getVar('username'),
                                    'bio'=>$request->getVar('bio'),
                               ])->update();

                if($update){
                    $user_info = $user->find($user_id);
                    return json_encode(['status'=>1,'user_info'=>$user_info,'msg'=>'Your personal details have been successfully updated.']);
                }else{
                    return json_encode(['status'=>0,'msg'=>'Something went wrong']);

                }               
            }
        }
    }

    public function updateProfilePicture(){ // https://youtu.be/MZ3LjAC-erc?t=773 START HERE TROUBLESHOOT
        $request = \Config\Services::request();
        $user_id = CIAuth::id();
        $user = new User;
        $user_info = $user->asObject()->where('id',$user_id)->first();

        $path = 'images/users/'; 
        $file = $request->getFile('user_profile_file');
        $old_picture = $user_info->picture;
        $new_filename = 'UIMG_'.$user_id.$file->getRandomName();

        // Image manipulation
        $upload_image = \Config\Services::image()
                      ->withFile($file)
                      ->resize(450,450,true,'height')
                      ->save($path.$new_filename);

        if($upload_image){
            if( $old_picture != null && file_exists($path.$new_filename) ){
                unlink($path.$old_picture);
            }
            $user->where('id',$user_info->id)
                 ->set(['picture'=>$new_filename])
                 ->update(); 
                 
            echo json_encode(['status'=>1,'msg'=>'Done! Your profile picture has been successfully updated.']);
        }else{
            echo json_encode(['status'=>0,'msg'=>'Something went wrong']);
        }
    }

    public function changePassword(){
        $request = \Config\Services::request();
        
        
            if( $request->isAjax() ){
                $validation = \Config\Services::validation();
        $user_id = CIAuth::id();
        $user = new User();
        $user_info = $user->asObject()->where('id',$user_id)->first();
                $this->validate([
                    'current_password'=>[
                        'rules'=>'required|min_length[5]|check_current_password[current_password]',
                        'errors'=>[
                            'required'=>'Enter current password',
                            'min_length'=>'Password must have a minimum of 5 characters',
                            'check_current_password'=>'The current password is incorrect',
                        ]
                    ],
                    'new_password'=>[
                        'rules'=>'required|min_length[5]|max_length[20]|is_password_strong[new_passsword]',
                        'errors'=>[
                            'required'=>'The password field is required',
                            'min-length'=>'The password must have a minmum of 5 characters',
                            'max-length'=>'The password must have a maximum of 20 characters',
                            'is_password_strong'=>'The password must contain at least 1 uppercase, 1 lowercase, 1 number and 1 special character'
                        ]
                    ],
                    'confirm_new_password'=>[
                        'rules'=>'required|matches[new_password]',
                        'errors'=>[
                            'required'=>'Confirm new password',
                            'matches'=>'Password mismatch',
                        ]
                    ]
                ]);

                if( $validation->run() === FALSE ) {
                    $errors = $validation->getErrors();
                    return $this->response->setJSON(['status'=>0, 'token'=>csrf_hash(),'error'=>$errors]);
                } else {
                    // Update user(admin) password
                    $user->where('id',$user_info->id)
                         ->set(['password'=>Hash::make($request->getVar('new_password'))])
                         ->update();

                        // Send Email notification
                        $mail_data = array(
                            'user'=>$user_info,
                            'new_password'=>$this->request->getVar('new_password')
                         );

                         $view = \config\Services::renderer();
                         $mail_body = $view->setVar('mail_data',$mail_data)->render('email-templates/password-changed-email-template');

                         $mailConfig = array(
                            'mail_from_email'=>env('EMAIL_FROM_ADDRESS'),
                            'mail_from_name'=>env('EMAIL_FROM_NAME'),
                            'mail_recipient_email'=>$user_info->email,
                            'mail_recipient_name'=>$user_info->name,
                            'mail_subject'=>'Profile password changed',
                            'mail_body'=>$mail_body
                        );

                        sendEmail($mailConfig);
                        return $this->response->setJSON(['status'=>1,'token'=>csrf_hash(),'msg'=>'Done! Your password has been successfully updated']);
                }
            
            }
    }

    public function settings(){
        $data = [
            'pageTitle'=>'Settings'
        ];
        return view('backend/pages/settings',$data);
    }

    public function updateGeneralSetting(){
        $request = \Config\Services::request();

        if( $request->isAjax() ){
            $validation = \Config\Services::validation();

            $this->validate([
                'blog_title'=>[
                    'rules'=>'required',
                    'errors'=>[
                        'required'=>'Blog title required'
                    ]
                    ],
                'blog_email'=>[
                    'rules'=>'required|valid_email',
                    'errors'=>[
                        'required'=>'Blog email required',
                        'valid_email'=>'Please use a valid email address'
                    ]
                ]
            ]);

            if( $validation->run() === FALSE ){
                $errors = $validation->getErrors();
                return $this->response->setJSON(['status'=>0,'token'=>csrf_hash(),'error'=>$errors]);
            }else{
                /* return $this->response->setJSON(['status'=>1,'token'=>csrf_hash(),'msg'=>'Good, form validated']); */
                $settings = new Setting();
                $setting_id = $settings->asObject()->first()->id;
                $update = $settings->where('id',$setting_id)
                                   ->set([
                                    'blog_title'=>$request->getVar('blog_title'),
                                    'blog_email'=>$request->getVar('blog_email'),
                                    'blog_phone'=>$request->getVar('blog_phone'),
                                    'blog_address'=>$request->getVar('blog_address'),
                                    'blog_meta_keywords'=>$request->getVar('blog_meta_keywords'),
                                    'blog_meta_description'=>$request->getVar('blog_meta_description')
                                   ])->update();

                if( $update ){
                    return $this->response->setJSON(['status'=>1,'token'=>csrf_hash(),'msg'=>'General settings have been updated successfully.']);
                }else{
                    return $this->response->setJSON(['status'=>0,'token'=>csrf_hash(),'msg'=>'Something went wrong.']);
                }                   
            }
        }
    }

    public function updateBlogLogo(){
        $request = \Config\Services::request();

        if( $request->isAJAX() ){
            $settings = new Setting();
            $path = 'images/blog/';
            $file = $request->getFile('blog_logo');
            $setting_data = $settings->asObject()->first();
            $old_blog_logo = $setting_data->blog_logo;
            $new_filename = 'CI4Blog_logo'.$file->getRandomName();

            if( $file->move($path, $new_filename) )
            {
                if( $old_blog_logo != null && file_exists($path.$old_blog_logo) ){
                    unlink($path.$old_blog_logo);
                }
            $update = $settings->where('id', $setting_data->id)
                                ->set(['blog_logo'=>$new_filename])
                                ->update();
                if( $update )
                {
                    return $this->response->setJSON(['status'=> 1,'token'=>csrf_hash(),'msg'=> 'Done! Blog logo has been successfully updated']);
                }
                else
                {
                    return $this->response->setJSON(['status'=> 0,'token'=>csrf_hash(),'msg'=> 'Something went wrong on uploading new logo']);
                }

            }
            else
            {
            return $this->response->setJSON(['status'=> 0,'token'=>csrf_hash(),'msg'=> 'Something went wrong on uploading the new logo.']);
            }

        }
    }

    public function updateBlogFavicon(){

        $request = \Config\Services::request();

        if( $request->isAJAX() ){
            $settings = new Setting();
            $path = 'images/blog/';
            $file = $request->getFile('blog_favicon');
            $setting_data = $settings->asObject()->first();
            $old_blog_favicon = $setting_data->blog_favicon;
            $new_filename = 'CI4Blog_favicon'.$file->getRandomName();

            if( $file->move($path, $new_filename) )
            {
                if( $old_blog_favicon != null && file_exists($path.$old_blog_favicon) ){
                    unlink($path.$old_blog_favicon);
                }
            $update = $settings->where('id', $setting_data->id)
                                ->set(['blog_favicon'=>$new_filename])
                                ->update();
                if( $update )
                {
                    return $this->response->setJSON(['status'=> 1,'token'=>csrf_hash(),'msg'=> 'Done! Blog favicon has been successfully updated']);
                }
                else
                {
                    return $this->response->setJSON(['status'=> 0,'token'=>csrf_hash(),'msg'=> 'Something went wrong on uploading new favicon']);
                }

            }
            else
            {
            return $this->response->setJSON(['status'=> 0,'token'=>csrf_hash(),'msg'=> 'Something went wrong on uploading the new favicon.']);
            }

        }

    }

    public function updateSocialMedia(){
        $request = \Config\Services::request();

        if( $request->isAJAX() ){
            $validation = \Config\Services::validation();
            $this->validate([
                'facebook_url'=>[
                    'rules'=> 'permit_empty|valid_url_strict',
                    'errors'=>[
                        'valid_url_string'=> 'Invalid Facebook page URL'
                    ]
                ],
                'twitter_url'=>[
                    'rules'=> 'permit_empty|valid_url_strict',
                    'errors'=>[
                        'valid_url_strict'=> 'Invalid Twitter URL'
                    ]
                ],
                'instagram_url'=>[
                    'rules'=> 'permit_empty|valid_url_strict',
                    'errors'=>[
                        'valid_url_strict'=> 'Invalid Instagram URL'
                    ]
                ],
                'youtube_url'=>[
                    'rules'=> 'permit_empty|valid_url_strict',
                    'errors'=>[
                        'valid_url_strict'=>'Invalid Youtube URL'
                    ]
                ],
                'linkedin_url'=>[
                    'rules'=> 'permit_empty|valid_url_strict',
                    'errors'=>[
                        'valid_url_strict'=> 'Invalid LinkedIn URL'
                    ]
                ],
                'github_url'=>[
                    'rules'=> 'permit_empty|valid_url_strict',
                    'errors'=>[
                        'valid_url_strict'=> 'Invalid GitHub URL'
                    ]
                ],
            ]);
            if( $validation->run() === FALSE ){
                $errors = $validation->getErrors();
                return $this->response->setJSON(['status'=> 0,'token'=>csrf_hash(),'error'=> $errors]);
            }else{
                /*return $this->response->setJSON(['status'=> 1,'token'=>csrf_hash(),'msg'=> 'Social Media links successfully updated']);*/
                $social_media = new SocialMedia();
                $social_media_id = $social_media->asObject()->first()->id;
                $update = $social_media->where('id',$social_media_id)
                                       ->set([
                                        'facebook_url'=>$request->getVar('facebook_url'),
                                        'twitter_url'=>$request->getVar('twitter_url'),
                                        'instagram_url'=>$request->getVar('instagram_url'),
                                        'youtube_url'=>$request->getVar('youtube_url'),
                                        'linkedin_url'=>$request->getVar('linkedin_url'),
                                        'github_url'=>$request->getVar('github_url'),
                                       ])
                                       ->update();
                                        if( $update ){
                                            return $this->response->setJSON([ 'status'=> 1,'token'=>csrf_hash(),'msg'=> 'Done! Blog social media has been successfully updated.']);
                                        }else{
                                            return $this->response->setJSON([ 'status'=> 0,'token'=>csrf_hash(),'msg'=> 'Something went wrong on updating blog social media.']);
                                        }
            }
        }
    }

    public function categories(){
        $data = [
            'pageTitle'=>'Categories'
        ];
        return view('backend\pages\categories', $data);
    }

    public function addCategory(){
        $request = \Config\Services::request();

        if( $request->isAJAX() ){
            $validation = \Config\Services::validation();
            $this->validate([
                'category_name'=>[
                        'rules'=> 'required|is_unique[categories.name]',
                       'errors'=>[
                     'required'=> 'Category name is required',
                    'is_unique'=> 'Category name is already in use'
                                 ]
                                ]
                            ]);
            if( $validation->run() === false ){
                $errors = $validation->getErrors();
                return $this->response->setJSON(['status'=>0,'token'=>csrf_hash(),'error'=> $errors]);
            }else{
                // return $this->response->setJSON(['status'=>1,'token'=>csrf_hash(),'msg'=>'Validate...']);
                $category = new Category();
                $save = $category->save(['name'=>$request->getVar('category_name')]);

                if( $save){
                    return $this->response->setJSON(['status'=> 1,'token'=>csrf_hash(),'msg'=> 'Category was successfully added.']);
                }else{
                    return $this->response->setJSON(['status'=> 0,'token'=>csrf_hash(),'msg'=> 'Something went wrong']);
                }
            }


        }
            
    }

    public function getCategories(){
        // DB details
        $dbDetails = array(
        'host'=>$this->db->hostname,
        'user'=>$this->db->username,
        'pass'=>$this->db->password,
        'db'=>$this->db->database
    );
    $table = 'categories';
    $primaryKey = 'id';
    $columns = array(
        array(
            'db'=>'id',
            'dt'=>0
        ),
        array(
            'db'=> 'name',
            'dt'=> 1
        ),
        array(
            'db'=> 'id',
            'dt'=> 2,
            'formatter'=>function($d, $row){
                // return '(x) will be added later';
                $subcategory = new SubCategory();
                $subcategories = $subcategory->where(['parent_cat'=>$row['id']])->findAll();
                return count($subcategories);
            }
        ),
        array(
            'db'=> 'id',
            'dt'=> 3,                
            'formatter'=>function($d, $row){
                return '<div class="btn-group">
                <button class="btn btn-sm btn-link p-0 mx-1 editCategoryBtn" data-id="'.$row['id'].'">Edit</button>
                <button class="btn btn-sm btn-link p-0 mx-1 deleteCategoryBtn" data-id="'.$row['id'].'">Delete</button>
                </div>';
            }
        ),
        array(
            'db'=> 'ordering',
            'dt'=> 4            
        ),
    );

    return json_encode(
        SSP::simple($_GET, $dbDetails, $table, $primaryKey, $columns)
    );


    }

    public function getCategory()
    {
        $request = \Config\Services::request();

        if( $request->isAJAX() ){
            $id = $request->getVar('category_id');
            $category = new Category();
            $category_data = $category->find($id);
            return $this->response->setJSON(['data'=>$category_data]);

        }
    }

        public function updateCategory()
        {
        $request = \Config\Services::request();

        if( $request->isAJAX() ){
            $id = $request->getVar('category_id');
            $validation = \Config\Services::validation();
            $this->validate([
                'category_name'=>[
                    'rules'=> 'required|is_unique[categories.name,id,'.$id.']',
                    'error'=>[
                        'required'=>'Category name is required',
                        'is_unique'=> 'a Category name like this already exists, please choose another name'
                    ]
                ]
            ]);
            if( $validation->run() === FALSE ){
                $errors = $validation->getErrors();
                return $this->response->setJSON(['status'=>0,'token'=>csrf_hash(), 'error'=>$errors]);
            }else{
                // return $this->response->setJSON(['status'=>1,'token'=>csrf_hash(),'msg'=>'Validated! ...']);
                $category = new Category();
                $update = $category->where('id',$id)
                                   ->set(['name'=>$request->getVar('category_name')])
                                   ->update();

                    if( $update ){
                        return $this->response->setJSON(['status'=> 1,'token'=>csrf_hash(),'msg'=> 'Category name successfully updated.']);

                    }else{

                        return $this->response->setJSON(['status'=> 0,'token'=>csrf_hash(),'msg'=> 'Something went wrong' ]);
                    }               

            }

            }
        }

        public function deleteCategory()
        {
            $request = \Config\Services::request();
            if( $request->isAJAX() ){
                 $id = $request->getVar('category_id');
                 $category = new Category();
                 // Future / Check for subcategories to delete

                 // Future / Check related posts for this category

                 // Delete Category
                 
                
                $subcategory = new SubCategory();
                $subcategories = $subcategory->where('parent_cat',$id)->findAll();
                if( $subcategories ){
                        $msg = count($subcategories) == 1 ? 'There is ('.count($subcategories).') Sub categories related to this Parent category, it cannot be deleted, first delete the sub categories related.' : 'There is ('.count($subcategories).') Sub categories related to this Parent category, it cannot be deleted, first delete the sub categories related.';
                        return $this->response->setJSON(['status'=> 0,'msg'=>$msg]);
                   }else{
                    $delete = $category->where('id',$id)->delete();        
                
                    if( $delete ){
                    return $this->response->setJSON(['status'=> 1, 'token'=>csrf_hash(),'msg'=> 'Category has been successfully deleted.']);
                    }else{
                    return $this->response->setJSON(['status'=> 0,'token'=>csrf_hash(),'msg'=> 'Something went wrong']);
                    }
                }
            }
        }
        public function reorderCategories()
        {
            $request = \Config\Services::request();

            if( $request->isAJAX() ){

                $positions = $request->getVar('positions');

                $category = new Category();

                foreach($positions as $position){
                    $index = $position[0];
                    $newPosition = $position[1];
                    $category->where('id',$index)
                             ->set(['ordering'=>$newPosition])
                             ->update();
                }
                return $this->response->setJSON(['status'=> 1,'msg'=> 'Categories ordering has been successfully updated.']);
            }
        }

        public function getParentCategories(){
            $request = \Config\Services::request();
            if( $request->isAJAX() ){
                $id = $request->getVar('parent_category_id');
                $options = '<option value="0">Uncategorized</option>';
                $category = new Category();
                $parent_categories = $category->findAll();

                if( count($parent_categories) ){
                    $added_options = '';
                    foreach($parent_categories as $parent_category){
                        $isSelected = $parent_category['id'] == $id ? 'selected' : '';
                        $added_options.='<option value="'.$parent_category['id'].'" '.$isSelected.'>'.$parent_category['name'].'</option>';
                    }
                    $options = $options.$added_options;
                    return $this->response->setJSON(['status'=> 1, 'data'=>$options]);
                }else{
                    return $this->response->setJSON(['status'=> 1, 'data'=>$options]);
                    
                }
            }                        
        }

        public function addSubCategory()
        {
            $request = \Config\Services::request();

            if( $request->isAJAX() ){
                $validation = \Config\Services::validation();

                $this->validate([
                    'subcategory_name'=>[
                        'rules'=>'required|is_unique[sub_categories.name]',
                        'errors'=>[
                            'required'=> 'Sub Category name is required',
                            'is_unique'=> 'This Sub Category name is already in use'
                        ],
                    ],
                ]);
                if( $validation->run() === FALSE ){
                    $errors = $validation->getErrors();
                    return $this->response->setJSON(['status'=> 0,'token'=>csrf_Hash(),'error'=>$errors]);
                }else{
                    // return $this->response->setJSON(['status'=> 1,'token'=>csrf_Hash(),'msg'=>'Validated....']);
                    $subcategory = new SubCategory();
                    $subcategory_name = $request->getVar('subcategory_name');
                    $subcategory_description = $request->getVar('description');
                    $subcategory_parent_category = $request->getVar('parent_cat');
                    $subcategory_slug = SlugService::model(SubCategory::class)->make($subcategory_name);

                    $save = $subcategory->save([
                        'name'=> $subcategory_name,
                        'parent_cat'=>$subcategory_parent_category,
                        'slug'=> $subcategory_slug,
                        'description'=> $subcategory_description
                    ]);

                    if($save){
                        return $this->response->setJSON(['status'=> 1, 'token'=>csrf_hash(), 'msg'=>'New Sub category has been added.']);
                    }else{
                        return $this->response->setJSON(['status'=> 0, 'token'=>csrf_hash() ,'msg'=> 'Something went wrong']);
                    }
                }
            }


        }

        public function getSubCategories(){
            $category = new Category();
            $subcategory = new SubCategory();

            //DB details
            $dbDetails = array(
                "host"=>$this->db->hostname,
                "user"=>$this->db->username,
                "pass"=>$this->db->password,
                "db"=>$this->db->database                
            );
            $table = "sub_categories";
            $primaryKey = "id";
            $columns = array(
                array(
                    "db"=>"id",
                    "dt"=>0
                ),
                array(
                    "db"=>"name",
                    "dt"=>1
                ),
                array(
                    "db"=>"id",
                    "dt"=>2,
                    "formatter"=>function($d, $row) use ($category, $subcategory)
                    {
                    $parent_cat_id = $subcategory->asObject()->where("id",$row['id'])->first()->parent_cat;
                    $parent_cat_name = ' - ';
                        if( $parent_cat_id != 0 )
                        {
                        $parent_cat_name = $category->asObject()->where('id',$parent_cat_id)->first()->name;
                        }
                        return $parent_cat_name;
                    }
                ),
                array(
                    "db"=>"id",
                    "dt"=>3,
                    "formatter"=>function($d, $row)
                    {
                        // return "(x) will be added later";
                        $post = new Post();
                        $posts = $post->where(['category_id'=>$row['id']])->findAll();
                        return count($posts);

                    }
                ),
                array(
                    "db"=>"id",
                    "dt"=>4,                    
                    "formatter"=>function($d, $row)
                    {
                        return "<div class='btn btn-group'>
                        <button class='btn btn-sm btn-link p-0 mx-1 editSubCategoryBtn' 
                        data-id='".$row['id']."'>Edit</button>
                        <button class='btn btn-sm btn-link p-0 mx-1 deleteSubCategoryBtn' 
                        data-id='".$row['id']."'>Delete</button>
                        </div>";
                    }
                ),
                array(
                    "db"=>"ordering",
                    "dt"=>5
                ),
            );

            return json_encode(
                SSP::simple($_GET, $dbDetails, $table, $primaryKey, $columns)
            );
        }

        public function getSubCategory()
        {
            $request = \Config\Services::request();

            if( $request->isAJAX() ){
                $id = $request->getVar("subcategory_id");
                $subcategory = new SubCategory();
                $subcategory_data = $subcategory->find($id);
                return $this->response->setJSON(['data'=>$subcategory_data]);
            }

        }

        public function updateSubCategory()
        {
            $request = \Config\Services::request();

            if( $request->isAJAX() )
            {
                $id = $request->getVar('subcategory_id');
                $validation = \Config\Services::validation();

                $this->validate(
                [
                    'subcategory_name'=>
                    [
                        'rules'=> 'required|is_unique[sub_categories.name,id,'.$id.']',
                        'errors'=>
                        [
                            'required'=> 'Sub Category name is required',
                            'is_unique'=> 'This Sub Category name is already in use',
                        ]
                    ]
                ]);
                if( $validation->run() === FALSE )
                {
                    $errors = $validation->getErrors();
                    return $this->response->setJSON(['status'=>0,'token'=>csrf_hash(),'error'=>$errors]);
                }
                else
                {
                    // return $this->response->setJSON(['status'=>1,'token'=>csrf_hash(),'msg'=>'Validated']);
                    $subcategory = new SubCategory();
                    $data = array(
                        'name'=> $request->getVar('subcategory_name'),
                        'parent_cat'=> $request->getVar('parent_cat'),
                        'description'=> $request->getVar('description')
                    );

                    $save = $subcategory->update($id,$data);

                    if( $save )
                    {
                        return $this->response->setJSON(['status'=> 1,'token'=>csrf_hash(),'msg'=> 'Sub Category was successfully updated.']);
                    }
                    else
                    {
                        return $this->response->setJSON(['status'=> 0,'token'=>csrf_hash(),'msg'=> 'Something went wrong']);
                    }

                }
            }
        }

        public function reorderSubCategories()
        {
            $request = \Config\Services::request();

            if( $request->isAJAX() )
            {
                $positions = $request->getVar('positions');

                $subcategory = new SubCategory();
                foreach( $positions as $position )
                {
                    $index = $position[0];
                    $newPosition = $position[1];
                    $subcategory->where('id',$index)
                                ->set(['ordering'=> $newPosition])
                                ->update();
                }
                return $this->response->setJSON(['status'=> 1,'msg'=> 'Subcategory order successfully changed.']);
            }
        }

        public function deleteSubCategory()
        {
            $request = \Config\Services::request();

            if( $request->isAJAX() ){
                 $id = $request->getVar('subcategory_id');
                 $subcategory = new SubCategory();

                 // Future / Check for subcategories to delete

                 // Future / Check related posts for this category

                 // Delete SubCategory
                 
                
                $post = new Post();
                $posts = $post->where('category_id',$id)->findAll();
                $msg = '';
                        if( $posts ){
                        $msg = count($posts) == 1 ? 'There is ('.count($posts).') post in this category. First move or delete the post before deleting this sub category' : 'There are ('.count($posts).') posts in this category. First move or delete the posts before deleting this sub category';
                        return $this->response->setJSON(['status'=> 0,'msg'=> $msg]);
                }else{
                    $delete = $subcategory->where('id',$id)->delete();        
                
                        if( $delete ){
                        return $this->response->setJSON(['status'=> 1, 'token'=>csrf_hash(),'msg'=> 'Subcategory has successfully been deleted.']);
                        }else{
                        return $this->response->setJSON(['status'=> 0,'token'=>csrf_hash(),'msg'=> 'Something went wrong']);
                }
                }
            }
        }

        public function addPost()
        {
             $subcategory = new SubCategory();
             $data = [
                'pageTitle'=>'Add new post',
                'categories'=>$subcategory->asObject()->findAll()
             ];
             return view('backend/pages/new-post',$data);
             
        }

        public function createPost()
        {
            $request = \Config\Services::request();
            
            if( $request->isAJAX() )
            {
                $validation = \Config\Services::validation();

                $this->validate([
                    'title'=>[
                        'rules'=>'required|is_unique[posts.title]',
                        'errors'=>[
                            'required'=>'Post title is required',
                            'is_unique'=>'This post title already exists.',
                        ]
                    ],
                    'content' =>[
                        'rules'=>'required|min_length[20]',
                        'errors'=>[
                            'required'=>'Content is required.',
                            'min_length'=>'Content must be more than 20 characters.',
                        ]
                    ],
                    'category'=>[
                        'rules'=>'required',
                        'errors'=>[
                            'required'=>'Please choose a category',
                        ]
                    ],
                    'featured-image'=>[
                        'rules'=>'uploaded[featured-image]|is_image[featured-image]|max_size[featured-image,2048]',
                        'errors'=>[
                            'uploaded'=>'Featured image is required',
                            'is_image'=>'Select an image file type',
                            'max_size'=>'Select an image that does not exceed 2MB in file size',
                        ]
                    ],

                ]);

                if( $validation->run() === FALSE )
                {
                    $errors = $validation->getErrors();
                   
                    return $this->response->setJSON(['status'=>0,'token'=>csrf_hash(),'error'=>$errors]);
                    
                }
                else
                {                    
                    $user_id = CIAuth::id();
                    $path = 'images/posts/';
                    $file = $request->getFile('featured-image');
                    // $filename = $file->getClientName();
                    $filename = 'pimg_'.time().$file->getClientName();

                    // Create directory if not exist
                    if( !is_dir($path) ){ mkdir($path,0777,true); }

                    // Upload featured image
                    if( $file->move($path,$filename)){
                        \config\Services::image()
                            ->withFile($path.$filename)
                            ->fit(150,150,'center')
                            ->save($path.'thumb_'.$filename);

                    // Create resized image
                    \config\Services::image()
                    ->withFile($path.$filename)
                    ->resize(450,300,true,'width')
                    ->save($path.'resized_'.$filename);

                    // Create resized image
                    \config\Services::image()
                    ->withFile($path.$filename)
                    ->resize(800,600,true,'width')
                    ->save($path.'large_'.$filename);

                    // Save new post details
                    $post = new Post();

                    $data = array(
                        'author_id'=> $user_id,
                        'category_id'=> $request->getVar('category'),
                        'title'=> $request->getVar('title'),
                        'slug'=>SlugService::model(Post::class)->make($request->getVar('title')),
                        'content'=> $request->getVar('content'),
                        'featured_image'=> $filename,
                        'tags'=>$request->getVar('tags'),
                        'meta_keywords'=> $request->getVar('meta_keywords'),
                        'meta_description'=> $request->getVar('meta_description'),
                        'visibility'=> $request->getVar('visibility'),                        
                    );

                    $save = $post->insert($data);
                    $last_id = $post->getInsertID();

                    if( $save )
                    {
                        return $this->response->setJSON(['status'=>1,'token'=>csrf_hash(),'msg'=> 'New post successfully added.']);
                    }
                    else
                    {
                        return $this->response->setJSON(['status'=> 0,'token'=>csrf_hash(),'msg'=> 'Something went wrong']);
                    }
                    
                    }else{
                        return $this->response->setJSON(['status'=> 0,'token'=>csrf_hash(),'msg'=> 'Error uploading file']);
                    }
                }
            }
        }

        public function allPosts()
        {
            $data = [
                'pageTitle'=> 'All Posts'
            ];
            return view('backend/pages/all-posts', $data);
        }

        public function getPosts() 
        {
            $dbDetails = array(
                "host"=>$this->db->hostname,
                "user"=>$this->db->username,
                "pass"=>$this->db->password,
                "db"=>$this->db->database
            );
            $table = "posts";
            $primaryKey = "id";
            $columns = array(
                array(
                    "db"=> "id",
                    "dt"=>0
                ),
                array(
                    "db"=>"id",
                    "dt"=>1,
                    "formatter"=>function($d, $row) 
                    {
                        $post = new Post();
                        $image = $post->asObject()->find($row['id'])->featured_image;
                        return "<img src='/images/posts/thumb_$image' class='img-thumbnail' style='max-width:70px;' /> ";
                    }
                ),
                array(
                    "db"=> "title",
                    "dt"=>2
                ),
                array(
                    "db"=> "id",
                    "dt"=>3,
                    "formatter"=>function($d, $row)
                    {
                        $post = new Post();
                        $category_id = $post->asObject()->find($row["id"])->category_id;
                        $subcategory = new SubCategory();
                        $category_name = $subcategory->asObject()->find($category_id)->name;
                        return "$category_name";
                    }
                ),
                array(
                    "db"=>"id",
                    "dt"=>4,
                    "formatter"=>function($d, $row)
                    {
                        $post = new Post();
                        $visibility = $post->asObject()->find($row["id"])->visibility;
                        return $visibility == 1 ? 'Public' : 'Private';
                    }
                
                ),
                array(
                    "db"=>"id",
                    "dt"=>5,
                    "formatter"=>function($d, $row)
                    {
                        return "<div class='btn btn-group'>
                            <a href='' class='btn btn-small btn-link p-0 mx-1'>View</a>
                            <a href='".route_to('edit-post',$row['id'])."' class='btn btn-small btn-link p-0 mx-1'>Edit</a>
                            <button class='btn btn-small btn-link p-0 mx-1 deletePostBtn' 
                            data-id='".$row['id']."'>Delete</button>
                        </div>";
                    }
                )
            ) ;
            return json_encode(
                SSP::simple($_GET,$dbDetails,$table,$primaryKey,$columns)
            );
        }

        public function editPost($id){
            $subcategory = new SubCategory();
            $post = new Post();
            $data = [
                'postTitle'=> 'Edit post',
                'categories'=> $subcategory->asObject()->findAll(),
                'post'=>$post->asObject()->find($id)
            ];
            return view('backend/pages/edit-post',$data);
        }

        public function updatePost()
        {
            $request = \Config\Services::request();

            if( $request->isAJAX() ){
                $validation = \Config\Services::validation();
                $post_id = $request->getVar('post_id');
                $user_id = CIAuth::id();
                $post = new Post();

                if( isset($_FILES['featured-image']['name']) && !empty($_FILES['featured-image']['name']) ){
                    $this->validate([
                        'title'=>[
                            'rules'=>'required',
                            'errors'=>[
                                'required'=>'Post title is required',
                            ]
                        ],
                        'content' =>[
                            'rules'=>'required|min_length[20]',
                            'errors'=>[
                                'required'=>'Content is required.',
                                'min_length'=>'Content must be more than 20 characters.',
                            ]
                        ],                        
                        'featured-image'=>[
                            'rules'=>'uploaded[featured-image]|is_image[featured-image]|max_size[featured-image,2048]',
                            'errors'=>[
                                'uploaded'=>'Featured image is required',
                                'is_image'=>'Select an image file type',
                                'max_size'=>'Select an image that does not exceed 2MB in file size',
                            ]
                        ]
    
                    ]);
                }else{
                    $this->validate([
                        'title'=>[
                            'rules'=>'required',
                            'errors'=>[
                                'required'=>'Post title is required',
                            ]
                        ],
                        'content' =>[
                            'rules'=>'required|min_length[20]',
                            'errors'=>[
                                'required'=>'Content is required.',
                                'min_length'=>'Content must be more than 20 characters.',
                            ]
                        ]            
                    ]);
                }

                if( $validation->run() === FALSE )
                {
                    $errors = $validation->getErrors();

                    return $this->response->setJSON( ['status'=> 0, 'token'=>csrf_hash(), 'error'=>$errors] );

                }
                else
                {
                    
                    if( isset($_FILES['featured-image']['name']) && !empty($_FILES['featured-image']['name'])){
                        $path = 'images/posts/';
                        $file = $request->getFile('featured-image');
                        // $filename = $file->getClientName();
                        $filename = 'pimg_'.time().$file->getClientName();
                        $old_post_featured_image = $post->asObject()->find($post_id)->featured_image;
            
                        // Upload featured image
                    if( $file->move($path,$filename)){
                        \config\Services::image()
                            ->withFile($path.$filename)
                            ->fit(150,150,'center')
                            ->save($path.'thumb_'.$filename);

                    // Create resized image
                    \config\Services::image()
                    ->withFile($path.$filename)
                    ->resize(450,300,true,'width')
                    ->save($path.'resized_'.$filename);

                    // Create resized image
                    \config\Services::image()
                    ->withFile($path.$filename)
                    ->resize(600,400,true,'width')
                    ->save($path.'large_'.$filename);

                            //Delete old featured image
                            if( $old_post_featured_image != null && file_exists($path.$old_post_featured_image) ){
                                unlink($path.$old_post_featured_image);
                            }

                            if( file_exists($path.'thumb_'.$old_post_featured_image) ){
                                unlink($path.'thumb_'.$old_post_featured_image);
                            }

                            if( file_exists($path.'resized_'.$old_post_featured_image) ){
                                unlink($path.'resized_'.$old_post_featured_image);
                            }

                            if( file_exists($path.'large_'.$old_post_featured_image) ){
                                unlink($path.'large_'.$old_post_featured_image);
                            }

                            // Update post details in database

                            $data = array(
                                'author_id'=>$user_id,
                                'category_id'=>$request->getVar('category'),
                                'title'=>$request->getVar('title'),
                                'slug'=>SlugService::model(Post::class)->make($request->getVar('title')),
                                'content'=>$request->getVar('content'),
                                'featured_image'=>$filename,
                                'tags'=>$request->getVar('tags'),
                                'meta_keywords'=>$request->getVar('meta_keywords'),
                                'meta_description'=>$request->getVar('meta_description'),
                                'visibility'=>$request->getVar('visibility'),
                            );

                            $update = $post->update($post_id,$data);

                            if( $update ){
                                return $this->response->setJSON(['status'=>1,'token'=>csrf_hash(),'msg'=>'Post successfully updated']);
                            }else{
                                return $this->response->setJSON(['status'=> 0,'token'=>csrf_hash(),'msg'=>'Post Update Failed']);
                            }

                        }else{
                            return $this->response->setJSON(['status'=> 0,'token'=> csrf_hash(),'error'=> 'Error on uploading featured image']);
                        }
                    }else{
                        // Update post details
                        $data = array(
                            'author_id'=>$user_id,
                            'category_id'=>$request->getVar('category'),
                            'title'=>$request->getVar('title'),
                            'slug'=>SlugService::model(Post::class)->make($request->getVar('title')),
                            'content'=>$request->getVar('content'),
                            'tags'=>$request->getVar('tags'),
                            'meta_keywords'=>$request->getVar('meta_keywords'),
                            'meta_description'=>$request->getVar('meta_description'),
                            'visibility'=>$request->getVar('visibility'),
                        );

                        $update = $post->update($post_id,$data);

                        if( $update ){
                            return $this->response->setJSON(['status'=> 1,'token'=>csrf_hash(),'msg'=> 'Post successfully updated']);
                        }else{
                            return $this->response->setJSON(['status'=> 0,'token'=>csrf_hash(),'error'=> 'Post update failed']);
                        }
                    }
                }
                
            }
        }

        public function deletePost()
        {
            $request = \Config\Services::request();
            
            if( $request->isAJAX() ){
                $path = 'images/posts/';
                $post_id = $request->getVar('post_id'); 
                //Get Data variable posted from delete page | $getJSON(url,{ post_id:post_id }, function(response)
                $post = new Post(); // New connection to DB
                $postInfo = $post->asObject()->find($post_id); // Get the post related to the $post_id
                $post_featured_image = $postInfo->featured_image; // Get path to featured image

                //Delete Post featured image
                if( $post_featured_image != null && file_exists($path.$post_featured_image) ){
                    unlink($path.$post_featured_image); // If the variable is not empty, and a path exists to the file, then unlink / delete the file.
                }
                if( file_exists($path.'thumb_'.$post_featured_image) ){
                    unlink($path.'thumb_'.$post_featured_image);
                }
                if( file_exists($path.'resized_'.$post_featured_image)){
                    unlink($path.'resized_'.$post_featured_image);
                }
                if( file_exists($path.'large_'.$post_featured_image)){
                    unlink($path.'large_'.$post_featured_image);
                }
                //now delete post data from db
                $delete = $post->delete($post_id);
                if( $delete ){
                    return $this->response->setJSON(['status'=> 1,'token'=>csrf_hash(),'msg'=> 'Post successfully deleted!']);
                }else{
                    return $this->response->setJSON(['status'=> 0,'token'=>csrf_hash(),'msg'=> 'Unable to delete post']);
                }


            }
        }
        
        
}