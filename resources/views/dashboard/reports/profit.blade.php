@extends('layouts.dashboard.app', ['datatable' => true])
@section('title')
تقرير الارباح و الخسائر 
@endsection

@push('css')
    <style>
        @media print {
            .title {
                text-align : center;
            }
            .print-hide {
                display: none !important
            }
        }
    </style>
@endpush

@section('content')
    <div class="box">
        <div class="box-header">
            <h3 class="title">
                تقرير الارباح و الخسائر
                <button class="btn btn-default pull-right print-hide" onclick="window.print()"> <i class="fa fa-print"></i> طباعة</button>
            </h3>
        </div>
        <div class="box-body">
            <table id="items-table" class="table table-bordered table-hover text-center">
                <thead>
                    <tr>
                        <th>الشهور</th>
                        <th>اجمالي المبيعات</th>
                        <th>اجمالي المنصرفات</th>
                        <th>الربح \ الخسارة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($months as $index=>$month)
                        <tr>
                            <td>{{ $month }}</td>
                            <td>{{ $orders[$index + 1] }}</td>
                            <td>{{ $all_expenses[$index] }}</td>
                            <td>{{( $orders[$index + 1] - $all_expenses[$index])  }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection