<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request; use Illuminate\Http\UploadedFile; use Illuminate\Support\Facades\File; use Illuminate\Support\Str;
class UserEmailTemplateController extends Controller {
 public function index(Request $request){$user=$request->user('user');return view('users.email.templates',['user'=>$user,'templates'=>$user->emailTemplates()->latest()->get()]);}
 public function store(Request $request){$data=$this->validated($request);$data['image_path']=$request->hasFile('image')?$this->storeImage($request->file('image'),$request->user('user')->id):null;unset($data['image']);$request->user('user')->emailTemplates()->create($data);return back()->with('status','Email template created successfully.');}
 public function update(Request $request,int $template){$item=$request->user('user')->emailTemplates()->findOrFail($template);$data=$this->validated($request);if($request->hasFile('image')){$this->deleteImage($item->image_path,$request->user('user')->id);$data['image_path']=$this->storeImage($request->file('image'),$request->user('user')->id);}unset($data['image']);$item->update($data);return back()->with('status','Email template updated successfully.');}
 public function toggleStatus(Request $request,int $template){$item=$request->user('user')->emailTemplates()->findOrFail($template);$item->update(['is_active'=>!$item->is_active]);return back()->with('status','Template status updated.');}
 public function destroy(Request $request,int $template){$item=$request->user('user')->emailTemplates()->findOrFail($template);$this->deleteImage($item->image_path,$request->user('user')->id);$item->delete();return back()->with('status','Email template deleted.');}
 private function validated(Request $r):array{return $r->validate(['name'=>['required','string','max:150'],'subject'=>['required','string','max:255'],'body'=>['required','string','max:30000'],'image'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],'is_active'=>['required','boolean']]);}
 private function storeImage(UploadedFile $f,int $id):string{$dir=public_path($id.'/email-templates');File::ensureDirectoryExists($dir,0755,true);$name=Str::uuid().'.'.$f->extension();$f->move($dir,$name);return $id.'/email-templates/'.$name;}
 private function deleteImage(?string $p,int $id):void{if($p&&str_starts_with($p,$id.'/email-templates/')&&File::isFile(public_path($p)))File::delete(public_path($p));}
}
