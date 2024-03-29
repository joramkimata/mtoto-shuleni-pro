<?php

class StudentController extends \BaseController
{

    /**
     * Display a listing of the resource.
     * GET /student
     *
     * @return Response
     */
    public function  changepassword()
    {
        $tid = Input::get('row_id');
        $password = Input::get('password_teacher');
        $user = User::find(Student::find($tid)->user_id);
        $user->password = Hash::make($password);
        $user->save();
    }

    public function admit()
    {
        sleep(1);
        return View::make('students.admit');
    }

    public function index()
    {
        //
        return View::make('students.index');
    }

    public function getSections()
    {
        $className = Input::get('className');
        return View::make('students.getSections')->withC($className);
    }

     public function getSubjects()
    {
        $sectName = Input::get('sectName');
        $class_name = Input::get('class_name');
        return View::make('students.getSubjects')->withS($sectName)->withC($class_name);
    }

    public function promotionx(){
        sleep(1);
        $class_name_from = Input::get('class_name_from');
        $class_name_to   = Input::get('class_name_to');
        $currentYear     = Input::get('currentYear');
        $promotedYear    = Input::get('promotedYear');
        $section_from    = Input::get('section_from');
        $section_to      = Input::get('section_to');
        $check = Student::where('class_name', $class_name_from)->where('section_name', $section_from)->get();
        if(count($check)){
            return View::make('students.promotionx')->withStudents($check)->withPromo(Input::all());
        }else{
            return '<div class="alert alert-danger"><i class="fa fa-warning"></i> No data Found for Student Promotion</div>';
        }
    }

    public function  promotion(){
        return View::make('students.promotion');
    }

    public function  doPromo(){
        $students = Input::get('students');
        $promo    = json_decode(Input::get('promo'));
        foreach($students as $student){
            $student_id = (integer)$student;
            $check = Studentpromotion::where('running_year', $promo->currentYear)->where('student_id', $student_id)->count();
            if($check){
                $sp = Studentpromotion::where('student_id', $student_id)->first();
                $sp->currentyear = $promo->currentYear;
                $sp->promotedyear = $promo->promotedYear;
                $sp->classfrom = $promo->class_name_from;
                $sp->classpromoted = $promo->class_name_to;
                $sp->sectionfrom = $promo->section_from;
                $sp->sectionto = $promo->section_to;
                $sp->running_year = $promo->promotedYear;
                $sp->save();

                $stx = Student::find($student_id);
                $stx->class_name = $promo->class_name_to;
                $stx->section_name = $promo->section_to;
                $stx->running_year = $promo->promotedYear;
                $stx->save();

            }else{
                // admit
                $sp = new Studentpromotion;
                $sp->currentyear = $promo->currentYear;
                $sp->promotedyear = $promo->promotedYear;
                $sp->classfrom = $promo->class_name_from;
                $sp->classpromoted = $promo->class_name_to;
                $sp->sectionfrom = $promo->section_from;
                $sp->sectionto = $promo->section_to;
                $sp->student_id = $student_id;
                $sp->running_year = $promo->promotedYear;
                $sp->save();

                $stx = Student::find($student_id);
                $stx->class_name = $promo->class_name_to;
                $stx->section_name = $promo->section_to;
                $stx->running_year = $promo->promotedYear;
                $stx->save();
            }
        }
    }


    public function refreshWith()
    {
        return Redirect::back()->withSuccess('Successfully processed!');
    }

    public function fetch()
    {
        $class_name = Input::get('class_name');
        return View::make('students.class')->withC($class_name);
    }

    /**
     * Show the form for creating a new resource.
     * GET /student/create
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * POST /student
     *
     * @return Response
     */

    public function  bulkImport()
    {
        return View::make('students.bulkImport');
    }

    public function  bulkImport_()
    {
        if (Input::hasFile('bulkImport')) {
            $file_path = 'students/excelfiles/' . explode('/',
                    HelperX::uplodFileThenReturnPath('bulkImport', 'students/excelfiles/'))[7];


            try {
                //excel codes starts here
                Excel::load($file_path, function ($reader) {

                    $results = ($reader->get(array(
                        'firstname',
                        'lastname',
                        'parent',
                        'username',
                        'password',
                        'class',
                        'section',
                        'admit'
                    )));


                    if (count($results->all()) == 0) {
                        throw new Exception('Excel has no data in it!');
                        //return Response::json(['error' => true, 'msg' => 'Excel has no data in it!']);
                    } else {

                        // scanning
                        foreach ($results->all() as $rr) {
                            $firstname = $rr["firstname"];
                            $lastname = $rr["lastname"];
                            $parentx = $rr["parent"];
                            $username = $rr["username"];
                            $password = $rr["password"];
                            $class = $rr["class"];
                            $section = $rr["section"];
                            $admitnumber = $rr["admit"];

                            $check = User::where('username', $username)->count();

                            if ($check) {
                                throw new Exception('Username already exists, please edit again the excel and import again');
                                //return Response::json(['error' => true, 'msg' => 'Username already exists, please edit again the excel and import again']);
                            } else {

                                $parent_check = Parentx::where('fullname', $parentx)->count();

                                if($parent_check){
                                    throw new Exception('Parent already exists, please edit again the excel and import again');
                                    //return Response::json(['error' => true, 'msg' => 'Parent already exists, please edit again the excel and import again']);
                                }

                                $check_ = Student::where('firstname', $firstname)->where('lastname', $lastname)->where('admit_number',
                                    $admitnumber)->count();

                                if($check_){
                                    throw new Exception('Student already exists, please edit again the excel and import again');
                                   // return Response::json(['error' => true, 'msg' => 'Student already exists, please edit again the excel and import again']);
                                }
                            }

                        }

                        // store students

                        foreach ($results->all() as $rr) {



                            $firstname = $rr["firstname"];
                            $lastname = $rr["lastname"];
                            $parent = $rr["parent"];
                            $username = $rr["username"];
                            $password = $rr["password"];
                            $class = $rr["class"];
                            $section = $rr["section"];
                            $admitnumber = $rr["admit"];

                            $user = new User;
                            $user->username = $username;
                            $user->firstname = $firstname;
                            $user->lastname = $lastname;
                            $user->role_id = Role::where('name', 'student')->first()->id;
                            $user->password = Hash::make($password);
                            $user->save();

                            $user_id = $user->id;

                            $s = new Student;
                            $s->firstname = $firstname;
                            $s->lastname = $lastname;
                            $s->class_name = $class;
                            $s->admit_number = $admitnumber;
                            $s->parent_id = Parentx::where('fullname', $parent)->first()->id;
                            $s->user_id = $user_id;
                            $s->save();

                        }

                        //return Response::json(['error' => false, 'msg' => 'Successfully added!']);

                    }
                })->get();

            } catch (Exception $x) {
                $fmsg = explode(':', $x->getMessage())[0];

                if($fmsg == "Undefined index"){
                    return Response::json(['error' => true, 'msg' => "Check your Excel File does not follow the diagram below"]);
                }else{
                    return Response::json(['error' => true, 'msg' => $x->getMessage()]);
                }
            }


        }

    }


    public function storex()
    {


        $firstname = Input::get('firstname');
        $lastname = Input::get('lastname');
        $parentx = Input::get('parentx');
        $username = Input::get('username');
        $class_name = Input::get('class_name');
        $password = Input::get('password');
        $admitnumber = Input::get('admitnumber');
        $status = Input::get('status');
        $birthday = Input::get('birthday');
        $gender = Input::get('gender');
        $address = Input::get('address');
        $phone = Input::get('phone');
        $email = Input::get('email');

        $check = User::where('username', $username)->count();

        if ($check) {
            return Response::json(['error' => true, 'msg' => 'Username already exists!']);
        } else {


            $check_ = Student::where('firstname', $firstname)->where('lastname', $lastname)->where('admit_number',
                $admitnumber)->count();

            if ($check_) {
                return Response::json(['error' => true, 'msg' => 'Student already exists!']);
            } else {

                $user = new User;
                $user->username = $username;
                $user->firstname = $firstname;
                $user->lastname = $lastname;
                $user->password = Hash::make($password);
                $user->active = $status;
                $user->role_id = Role::where('name', 'student')->first()->id;
                $user->save();

                $user_id = $user->id;

                $s = new Student;
                $s->firstname = $firstname;
                $s->lastname = $lastname;
                $s->class_name = $class_name;
                $s->admit_number = $admitnumber;
                $s->birthday = $birthday;
                $s->gender = $gender;
                $s->address = $address;
                $s->phone = $phone;
                $s->status = $status;
                $s->email = $email;
                $s->running_year = date('Y');
                $s->parent_id = $parentx;
                $s->user_id = $user_id;

                if (Input::has('section')) {
                    $s->section_name = Input::get('section');
                }

                if (Input::hasFile("profile_photo_edit")) {
                    $s->profile_photo = HelperX::uplodFileThenReturnPath('profile_photo_edit');
                }

                $s->save();

                return Response::json(['error' => false, 'msg' => 'Successfully Saved']);


            }


        }


    }

    public function store()
    {


        $firstname = Input::get('firstname');
        $lastname = Input::get('lastname');
        $parentx = Input::get('parentx');
        $username = Input::get('username');
        $class_name = Input::get('class_name');
        $password = Input::get('password');
        $admitnumber = Input::get('admitnumber');
        $status = Input::get('status');
        $birthday = Input::get('birthday');
        $gender = Input::get('gender');
        $address = Input::get('address');
        $phone = Input::get('phone');
        $email = Input::get('email');

        $check = User::where('username', $username)->count();

        if ($check) {
            return Response::json(['error' => true, 'msg' => 'Username already exists!']);
        } else {


            $check_ = Student::where('firstname', $firstname)->where('lastname', $lastname)->where('admit_number',
                $admitnumber)->count();

            if ($check_) {
                return Response::json(['error' => true, 'msg' => 'Student already exists!']);
            } else {

                $user = new User;
                $user->username = $username;
                $user->firstname = $firstname;
                $user->lastname = $lastname;
                $user->password = Hash::make($password);
                $user->active = $status;
                $user->role_id = Role::where('name', 'student')->first()->id;
                $user->save();

                $user_id = $user->id;

                $s = new Student;
                $s->firstname = $firstname;
                $s->lastname = $lastname;
                $s->class_name = $class_name;
                $s->admit_number = $admitnumber;
                $s->birthday = $birthday;
                $s->gender = $gender;
                $s->address = $address;
                $s->phone = $phone;
                $s->status = $status;
                $s->email = $email;
                $s->running_year = date('Y');
                $s->parent_id = $parentx;
                $s->user_id = $user_id;

                if (Input::has('section')) {
                    $s->section_name = Input::get('section');
                }

                if (Input::hasFile("studentphoto")) {
                    $s->profile_photo = HelperX::uplodFileThenReturnPath('studentphoto');
                }

                $s->save();

                return Response::json(['error' => false, 'msg' => 'Successfully Saved']);


            }


        }


    }

    /**
     * Display the specified resource.
     * GET /student/{id}
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     * GET /student/{id}/edit
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        $student_id = Input::get('student_id');
        $student = Student::find($student_id);
        return View::make('students.edit')->withStudent($student);
    }

    /**
     * Update the specified resource in storage.
     * PUT /student/{id}
     *
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {
        $row_id = Input::get('row_id');

        $firstname = Input::get('firstname');
        $lastname = Input::get('lastname');
        $parentx = Input::get('parentx');
        $username = Input::get('username');
        $class_name = Input::get('class_name');
        $admitnumber = Input::get('admitnumber');
        $status = Input::get('status');
        $birthday = Input::get('birthday');
        $gender = Input::get('gender');
        $address = Input::get('address');
        $phone = Input::get('phone');
        $email = Input::get('email');

        $check = User::where('username', $username)->where('id', Student::find($row_id)->user_id)->count();

        if ($check) {
            //update
            $check_2 = Student::where('id', $row_id)->where('firstname', $firstname)->where('lastname',
                $lastname)->where('admit_number', $admitnumber)->count();
            if ($check_2) {
                //update
                $s = Student::find($row_id);
                $s->firstname = $firstname;
                $s->lastname = $lastname;
                $s->class_name = $class_name;
                $s->admit_number = $admitnumber;
                $s->birthday = $birthday;
                $s->gender = $gender;
                $s->address = $address;
                $s->phone = $phone;
                $s->status = $status;
                $s->email = $email;
                $s->parent_id = $parentx;

                if (Input::has('section')) {
                    $s->section_name = Input::get('section');
                }

                if (Input::hasFile("profile_photo_edit")) {
                    $s->profile_photo = HelperX::uplodFileThenReturnPath('profile_photo_edit');
                }

                $s->save();
                return Response::json(['error' => false, 'msg' => 'Successfully']);
            } else {
                $check_21 = Student::where('id', '!=', $row_id)->where('firstname', $firstname)->where('lastname',
                    $lastname)->where('admit_number', $admitnumber)->count();
                if ($check_21) {
                    return Response::json(['error' => true, 'msg' => 'Student already taken!']);
                } else {
                    //update
                    $s = Student::find($row_id);
                    $s->firstname = $firstname;
                    $s->lastname = $lastname;
                    $s->class_name = $class_name;
                    $s->admit_number = $admitnumber;
                    $s->birthday = $birthday;
                    $s->gender = $gender;
                    $s->address = $address;
                    $s->phone = $phone;
                    $s->status = $status;
                    $s->email = $email;
                    $s->parent_id = $parentx;

                    if (Input::has('section')) {
                        $s->section_name = Input::get('section');
                    }

                    if (Input::hasFile("profile_photo_edit")) {
                        $s->profile_photo = HelperX::uplodFileThenReturnPath('profile_photo_edit');
                    }

                    $s->save();
                    return Response::json(['error' => false, 'msg' => 'Successfully']);
                }
            }
        } else {
            $check_ = User::where('username', $username)->where('id', '!=', Student::find($row_id)->user_id)->count();
            if ($check_) {
                return Response::json(['error' => true, 'msg' => 'Username already taken!']);
            } else {
                //update
                $u = User::find(Student::find($row_id)->user_id);
                $u->firstname = $firstname;
                $u->lastname = $lastname;
                $u->username = $username;
                $u->active = $status;
                $u->save();
                $check_2 = Student::where('id', $row_id)->where('firstname', $firstname)->where('lastname',
                    $lastname)->where('admit_number', $admitnumber)->count();
                if ($check_2) {
                    //update
                    $s = Student::find($row_id);
                    $s->firstname = $firstname;
                    $s->lastname = $lastname;
                    $s->class_name = $class_name;
                    $s->admit_number = $admitnumber;
                    $s->birthday = $birthday;
                    $s->gender = $gender;
                    $s->address = $address;
                    $s->phone = $phone;
                    $s->status = $status;
                    $s->email = $email;
                    $s->parent_id = $parentx;

                    if (Input::has('section')) {
                        $s->section_name = Input::get('section');
                    }

                    if (Input::hasFile("profile_photo_edit")) {
                        $s->profile_photo = HelperX::uplodFileThenReturnPath('profile_photo_edit');
                    }

                    $s->save();
                    return Response::json(['error' => false, 'msg' => 'Successfully']);
                } else {
                    $check_21 = Student::where('id', '!=', $row_id)->where('firstname', $firstname)->where('lastname',
                        $lastname)->where('admit_number', $admitnumber)->count();
                    if ($check_21) {
                        return Response::json(['error' => true, 'msg' => 'Student already taken!']);
                    } else {
                        //update
                        $s = Student::find($row_id);
                        $s->firstname = $firstname;
                        $s->lastname = $lastname;
                        $s->class_name = $class_name;
                        $s->admit_number = $admitnumber;
                        $s->birthday = $birthday;
                        $s->gender = $gender;
                        $s->address = $address;
                        $s->phone = $phone;
                        $s->status = $status;
                        $s->email = $email;
                        $s->parent_id = $parentx;

                        if (Input::has('section')) {
                            $s->section_name = Input::get('section');
                        }

                        if (Input::hasFile("profile_photo_edit")) {
                            $s->profile_photo = HelperX::uplodFileThenReturnPath('profile_photo_edit');
                        }

                        $s->save();
                        return Response::json(['error' => false, 'msg' => 'Successfully']);
                    }
                }
            }
        }


    }

    /**
     * Remove the specified resource from storage.
     * DELETE /student/{id}
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        $student_id = Input::get('student_id');
        Student::find($student_id)->delete();
    }

}