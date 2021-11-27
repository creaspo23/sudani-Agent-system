@extends('layouts.dashboard.app')

@section('title')
    عرض
@endsection

@section('content')
    @component('partials._breadcrumb')
        @slot('title', ['الوحدات','عرض'])
        @slot('url', [route('credits.index'), '#'])
        @slot('icon', ['users', 'eye'])
    @endcomponent
    <div class="row">
        <div class="col-md-8">
            <div class="box">
                <div class="box-header">
                    <h3>{{ $credit->name }}</h3>
                </div>
            </div>
        </div>
    </div>
<form action="{{ route('credits.destroy', $credit->id) }}" method="POST">
        @csrf
        {{ method_field('DELETE') }}
        <div class="row">
            <div class="col-md-8">
                <div class="box">
                    <div class="box-header">
                        <table class="table table-borderd">
                            <tr>
                                <th>التاريخ</th>
                                <td>{{ $credit->date }}</td>
                                <th>النسبة</th>
                                <td>{{ $credit->bonus }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="box-footer">
                        @if(request()->delete)
                            @permission('credits-delete')
                                <button type="submit" class="btn btn-danger btn-sm delete"><i class="fa fa-trash"></i> حذف </button>
                            @endpermission
                        @else
                            @permission('credits-delete')
                                <a href="{{ route('credits.edit', $credit->id) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i>  تعديل </a>
                            @endpermission
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection