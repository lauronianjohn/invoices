@extends('layouts.login')
@section('content')
    <div class="row">
        <div class="col-lg-3 margin-tb">
            <div class="pull-left">
            </div>
             <div class="pull-right">
                <a class="btn btn-primary" href="javascript:history.back()"> Back</a>
            </div></br>
            </br>
            </br>
        </div>
    </div>
    <div class="card">
        <div class="parse-content">
            <div class="container">
                <div class="category">
                       <p>{{$parses->parse}}</p>
                </div>
            </div>    
            </table>
        </div>
    </div>
@endsection