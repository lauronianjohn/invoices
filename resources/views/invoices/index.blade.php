@extends('layouts.login')
<style>
table {
   overflow-y: auto;
   height:550px;
   display:block;
   layout: absolute;
}
</style>
@section('title')
    Dashboard
@endsection

@section('extra-css')
    
    <!-- JQuery DataTable Css -->
    {{ Html::style('bsbmd/plugins/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css') }}
@endsection

@section('content')
    <script src="{{asset('js/animation.js')}}"></script>
    <div class="container-fluid">
        <div class="block-header">
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                @if(empty($comp_name))
                                    
                                @else
                                    {{$comp_name->company_name}}
                                @endif
                            </h2>
                            <div class="choose-company">
                                <form action="/invoices/dropdown" method="post" style="display: inline-block;margin-top: -6px;">
                                    {{ csrf_field() }}
                                    <div class="search-section">
                                        <div class="select-section" style="width: 227px;float: left;margin-right: 7px;">
                                            <select class="form-control" name="select" style="width: 268px;">
                                                    <option value="">--- Choose Company ---</option>
                                                @foreach($company as $companies)
                                                    <option value="{{$companies->id}}">{{$companies->company_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="button-search">
                                        <button type="submit" class="btn btn-primary">Go</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="body">
                             @if(count($invoices) == 0)
                            <br>
                                <div class="alert bg-red alert-dismissible" role="alert">
                                    <strong>No Record Found</strong>
                                </div>
                            @else
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover js-basic-example dataTable">
                                    <thead>
                                        <tr>
                                            <th style="position: sticky; top: 0px; background: white; width: 242px;">Name</th>
                                            <th style="position: sticky; top: 0px; background: white; width: 216px;">Form_assign</th>
                                            <th style="position: sticky; top: 0px; background: white; width: 252px;">File Name</th>
                                            <th style="position: sticky; top: 0px; background: white; width: 129px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($invoices as $invoice)
                                            <tr>
                                                <td>{{ $invoice->invoice_name }}</td>
                                                <td>{{ $invoice->form_name}}</td>
                                                <td>{{ $invoice->file_location }}</td>
                                                <td>
                                                    <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" onclick="window.location='{{ route("invoices.show", $invoice->id) }}';" class="btn bg-teal btn-block">SHOW</button>
                                                        <button type="button" onclick="window.location='{{ route("invoices.edit", $invoice->id) }}';" class="btn bg-cyan btn-block">EDIT</button>
                                                        <button type="submit" class="btn bg-red btn-block">DELETE</button>    
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach                                    
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('extra-script')
        {{Html::script('bsbmd/plugins/jquery-countto/jquery.countTo.js')}}
        {{Html::script('bsbmd/plugins/raphael/raphael.min.js')}}
        {{Html::script('bsbmd/plugins/morrisjs/morris.js')}}
        {{Html::script('bsbmd/plugins/chartjs/Chart.bundle.js')}}
        {{Html::script('bsbmd/plugins/flot-charts/jquery.flot.js')}}
        {{Html::script('bsbmd/plugins/flot-charts/jquery.flot.resize.js')}}
        {{Html::script('bsbmd/plugins/flot-charts/jquery.flot.pie.js')}}
        {{Html::script('bsbmd/plugins/flot-charts/jquery.flot.categories.js')}}
        {{Html::script('bsbmd/plugins/flot-charts/jquery.flot.time.js')}}-->
        {{Html::script('bsbmd/plugins/jquery-sparkline/jquery.sparkline.js')}}
        {{Html::script('bsbmd/js/pages/index.js')}}
        
        
        <!-- Jquery DataTable Plugin Js -->
        {{Html::script('bsbmd/plugins/jquery-datatable/jquery.dataTables.js')}}
        {{Html::script('bsbmd/plugins/jquery-datatable/skin/bootstrap/js/dataTables.bootstrap.js')}}
        {{Html::script('bsbmd/plugins/jquery-datatable/extensions/export/dataTables.buttons.min.js')}}
        {{Html::script('bsbmd/plugins/jquery-datatable/extensions/export/buttons.flash.min.js')}}
        {{Html::script('bsbmd/plugins/jquery-datatable/extensions/export/jszip.min.js')}}
        {{Html::script('bsbmd/plugins/jquery-datatable/extensions/export/pdfmake.min.js')}}
        {{Html::script('bsbmd/plugins/jquery-datatable/extensions/export/vfs_fonts.js')}}
        {{Html::script('bsbmd/plugins/jquery-datatable/extensions/export/buttons.html5.min.js')}}
        {{Html::script('bsbmd/plugins/jquery-datatable/extensions/export/buttons.print.min.js')}}
        
        
        {{Html::script('bsbmd/js/pages/tables/jquery-datatable.js')}}

@endsection
