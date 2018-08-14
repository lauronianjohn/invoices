<?php

namespace App\Http\Controllers;

use App\Invoice;
use App\Company;
use App\Formname;
use App\InvoiceInput;
use File;
use Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class InvoicesController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $company = Company::orderBy('company_name', 'asc')->get();
        $comp = Company::all()->first();
        $invoices = DB::table('invoices')
                        ->select('invoices.id','companies.company_name','formnames.form_name','invoices.file_location','invoices.invoice_name')
                        ->join('formnames', 'invoices.form_name_id', '=', 'formnames.id')
                        ->join('companies', 'invoices.company_id', '=', 'companies.id')
                        ->where('invoices.company_id', $comp->id)
                        ->orderBy('invoices.created_at', 'Desc')
                        ->paginate(5);
        $comp_name = Company::find($comp->id);
        return view('invoices.index',compact('invoices','company','comp_name'))
                        ->with('i', (request()->input('page', 1) - 1) * 5);
    }
    public function create()
    {
        $companies = Company::orderBy('company_name', 'asc')->get();
        $formname = Formname::all();
        return view('invoices.create', compact('companies','formname'));
    }
    public function store(Request $request)
    {
        $this->validate(request(),[
            'file' => 'required | mimes:jpeg,jpg,png,pdf',
            'company_id' => 'required',
            'invoice_name' => 'required | unique:invoices,invoice_name',
        ]);
        $img = request('file');
        $img_name = $img->getClientOriginalName();
        $company = Company::find(request('company_id'));
        $company_path = 'images/'. $company->company_name;
        DB::beginTransaction();
        try{
            $inv = new Invoice;
            $path = $company_path;
            $inv->company_id = request('company_id');
            $inv->invoice_name = request('invoice_name');
            $inv->form_name_id = request('assign_form');
            $inv->file_location = $img_name;
            if(file_exists($company_path. '/' .$img_name)){
                Session::flash('error', 'file already exist');
                return redirect(route('invoices.create', compact('error')));
            }
            else{
                if($img->move($company_path,$img_name)){
                    if(!$inv->save()){
                        Session::flash('error', 'Theres a problem on saving data');
                        if(File::delete($company_path. '/' .$img_name)){
                            Session::flash('error', 'Theres a problem on rollback the file');
                        }
                    }                                
                }
                else{
                    Session::flash('error', 'Theres a problem on saving file');   
                }
            }
        }
        catch(Exception $e){
            DB::rollback();
            throw $e;
        }
        $form = request('assign_form');
        DB::commit();
        if($form == null){
            return redirect(route('invoices.no_form_inv'))->with('success', 'Created successfully');
        }
        else
        {
            return redirect(route('invoices.index'))->with('success', 'Created successfully');
        }
    }
    public function show($id)//for show button in 'invoice'
    {
        $url = request()->getHttpHost();//get the url
        $invoice = DB::table('invoices')
                            ->select('invoices.id','companies.company_name','formnames.form_name','invoices.file_location','invoices.invoice_name')
                            ->join('formnames', 'invoices.form_name_id', '=', 'formnames.id')
                            ->join('companies', 'invoices.company_id', '=', 'companies.id')
                            ->where('invoices.id', $id)
                            ->orderBy('invoices.created_at', 'Desc')
                            ->first();
        $extension = \File::extension($invoice->file_location);
        return view('invoices.show',compact('invoice','extension','url'));
    }
    public function show_without_form($id)// for show button in 'invoice w/o form'
    {
        $url = request()->getHttpHost();//get the url
        $invoice = DB::table('invoices')
                            ->select('invoices.id','companies.company_name','invoices.file_location','invoices.invoice_name','invoices.form_name_id')
                            ->join('companies', 'invoices.company_id', '=', 'companies.id')
                            ->whereNull('invoices.form_name_id')
                            ->where('invoices.id', $id)
                            ->orderBy('invoices.created_at', 'Desc')
                            ->first();
        if($invoice->form_name_id == null)
        {
            $invoice->form_name = "";
        }
        $extension = \File::extension($invoice->file_location);
        return view('invoices.show',compact('invoice','display','extension','url'));
    }
    public function assign_form($id)
    {
        $invoice = DB::table('invoices')
                            ->select('invoices.id','companies.id AS companies_id','companies.company_name','invoices.file_location','invoices.invoice_name','invoices.form_name_id')
                            ->join('companies', 'invoices.company_id', '=', 'companies.id')
                            ->whereNull('invoices.form_name_id')
                            ->where('invoices.id', $id)
                            ->orderBy('invoices.created_at', 'Desc')
                            ->first();
        $form = Formname::where('company_id', $invoice->companies_id)->get();
        $extension = \File::extension($invoice->file_location);
        return view('invoices.assign_form',compact('invoice','display','extension','form'));
    }

    public function edit($id)
    {
        $companies = Company::orderBy('company_name', 'asc')->get();
        $formname = Formname::all();
         $invoices = Invoice::select('companies.company_name','invoices.invoice_name','invoices.file_location','companies.id AS company_id','invoices.id')
                    ->join('companies', 'invoices.company_id', '=', 'companies.id')
                    ->where('invoices.id',$id)
                    ->first();
        // dd($invoices);
        return view('invoices.edit',compact('companies','formname','invoices'));
    }
    public function update(Request $request, $id)
    {
         $this->validate(request(),[
            'file' => 'required | mimes:jpeg,jpg,png,pdf',
            'company_id' => 'required',
            'invoice_name' => 'required | unique:invoices,invoice_name,'.$id.',id'
        ]);
        $img = request('file');
        $img_name = $img->getClientOriginalName();
        $company = Company::find(request('company_id'));
        $company_path = 'images/'. $company->company_name;
        DB::beginTransaction();
        try{
            $inv = Invoice::find($id);
            $oldfile = $inv->file_location;//if company updated
            $oldcomp = Company::find($inv->company_id);
            $oldcompid = $oldcomp->id;
            $oldpath = 'images/'. $oldcomp->company_name;//end
            $newcomp = request('company_id');
            $inv->company_id = request('company_id');
            $inv->invoice_name = request('invoice_name');
            $inv->form_name_id = request('form_name_id');
            $inv->file_location = $img_name;
            if(file_exists($company_path. '/' .$img_name)){
                Session::flash('error', 'file already exist');
            }
            else{
                if($img->move($company_path,$img_name)){
                    if(!$inv->save()){
                        if(File::delete($company_path. '/' .$img_name)){
                            Session::flash('error', 'Theres a problem on rollback the file');
                        }
                        Session::flash('error', 'Theres a problem on saving data');
                    }
                    else{
                        if($oldcompid == $newcomp){
                            File::delete($company_path. '/' .$oldfile);
                        }
                        else{
                            File::delete($oldpath. '/' .$oldfile);
                        }
                    }                                
                }
                else{
                    Session::flash('error', 'Theres a problem on saving file');   
                }
            }
        }
        catch(Exception $e){
            DB::rollback();
            throw $e;
        }
        DB::commit();
        return redirect(route('invoices.form_without'))->with('success', 'Updated successfully');
    }

    public function destroy($id)
    {
        $invoices = Invoice::findorFail($id);
        $company = Company::find($invoices->company_id);
        
        if(!File::delete($company->company_name.'/'.$invoices->file_location)){
            dd($company->company_name, $invoices->file_location);
        }
        else{
            $invoices->delete();
        }
        return redirect()->route('invoices.no_form_inv')
                        ->with('success','Invoice entry deleted successfully');
    }
    public function form_without()
    {
        $comp = Company::all()->first();
        $invoices = DB::table('invoices')
                            ->select('invoices.id','companies.company_name','invoices.file_location','invoices.invoice_name')
                            ->join('companies', 'invoices.company_id', '=', 'companies.id')
                            ->whereNull('invoices.form_name_id')
                            ->where('invoices.company_id', $comp->id)
                            ->orderBy('invoices.created_at', 'Desc')
                            ->paginate(5);
        $company = Company::orderBy('company_name', 'asc')->get();
        $comp_name = Company::find($comp->id);
         return view('invoices.no_form_inv',compact('invoices','company','comp_name'))
                        ->with('i', (request()->input('page', 1) - 1) * 5);
    }
    public function form_without_select()
    {
        $comp_req = request('select_n');
        $company = Company::orderBy('company_name', 'asc')->get();
        $invoices = DB::table('invoices')
                            ->select('invoices.id','companies.company_name','invoices.file_location','invoices.invoice_name')
                            ->join('companies', 'invoices.company_id', '=', 'companies.id')
                            ->whereNull('invoices.form_name_id')
                            ->where('invoices.company_id', $comp_req)
                            ->orderBy('invoices.created_at', 'Desc')
                            ->paginate(5);
        $comp_name = Company::find($comp_req);
        return view('invoices.no_form_inv',compact('invoices','company','comp_name'))
                        ->with('i', (request()->input('page', 1) - 1) * 5);
    }
    public function dropdown()//select button from invoice
    {
        $select = request('select');
        $company = Company::all();
        $invoices = DB::table('invoices')
                        ->select('invoices.id','companies.company_name','formnames.form_name','invoices.file_location','invoices.invoice_name')
                        ->join('formnames', 'invoices.form_name_id', '=', 'formnames.id')
                        ->join('companies', 'invoices.company_id', '=', 'companies.id')
                        ->where('invoices.company_id', $select)
                        ->orderBy('invoices.created_at', 'Desc')
                        ->paginate(5);
        $comp_name = Company::find($select);
        return view('invoices.index',compact('invoices','company','comp_name'))
                        ->with('i', (request()->input('page', 1) - 1) * 5);
    }
    public function ajax($id){
        $forms = InvoiceInput::where('form_name_id', $id)->get();
        return response()->json($forms);
    }
    public function update_assign(Request $request, $id){
        $this->validate(request(),[ 
            'form_name' => 'required'
        ]);
        if(request('form_name')){
            $inv = Invoice::find($id);
            $inv->form_name_id = request('form_name');
            if($inv->save()){
                return redirect(route('invoices.index'))->with('success','Assigned successfully');
            }
        }
    }
    public function company_ajax($id){
        $formnames = Formname::where('company_id', $id)->get();
        return response()->json($formnames);
    }
}

?>
