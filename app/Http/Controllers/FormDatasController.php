<?php

namespace App\Http\Controllers;
use App\Company;
use App\Formname;
use App\InvoiceInput;
use App\Invoice;
use DB;
use App\FormData;
use Illuminate\Http\Request;

class FormDatasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list()
    {
        $form = Formname::all();
        $formname = Formname::all()->first();
        $invoices = DB::table('invoices')
                        ->select(
                            'invoices.id',
                            'invoices.invoice_name',
                            'companies.company_name',
                            'invoices.file_location'
                        )
                        ->join('companies', 'invoices.company_id', '=', 'companies.id')
                        ->where('form_name_id', $formname->id)
                        ->get();
        return view('parse/list', compact('form','formname','invoices'));
    }
    public function store(Request $request)
    {
        // $data = $request->all();
        // dd($data);
        DB::beginTransaction();
        try{
            foreach($request->value as $key => $value){
                $data = array('invoice_id' => $request->invoice_id,
                        'formname_id' => $request->formname_id,
                        'value' => $request->value [$key]);
                FormData::create($data);
            }
        }
        catch(Exception $e){
            DB::rollback();
            throw $e;
        }
        DB::commit();
        return redirect(route('parse.list'))->with('success', 'Created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $invoice = Invoice::select('invoices.id','invoices.file_location','companies.company_name','invoices.form_name_id')
                        ->join('companies', 'invoices.company_id', '=', 'companies.id')
                        ->where('invoices.id', $id)
                        ->first();
        $form = InvoiceInput::all()
                            ->where('form_name_id', $invoice->form_name_id);
        return view('parse/parse', compact('invoice','form'));
    }
    public function show_data($id){
        $data = FormData::where('invoice_id', $id)->get();
        $invoice = Invoice::where('id', $id)->first();
        
        return view('parse/show_data', compact('data','invoice'));
    }
    public function search_form(){
        $search = request('search_form');
        $form = Formname::all();
        $formname = Formname::find($search);
        $invoices = DB::table('invoices')
                        ->select(
                            'invoices.id',
                            'invoices.invoice_name',
                            'companies.company_name',
                            'invoices.file_location'
                        )
                        ->join('companies', 'invoices.company_id', '=', 'companies.id')
                        ->where('form_name_id', $search)
                        ->get();
        return view('parse/list', compact('form','formname','invoices'));
    }
}