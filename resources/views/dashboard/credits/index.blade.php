@extends('layouts.dashboard.app', ['modals' => ['credit'], 'datatable' => true])

@section('title')
العمولة والنسبة
@endsection

@section('content')
    @component('partials._breadcrumb')
        @slot('title', ['العمولة'])
        @slot('url', ['#'])
        @slot('icon', ['users'])
    @endcomponent
    <div class="box">
        <div class="box-header">
            @permission('units-create')
                <button type="button" style="display:inline-block; margin-left:1%" class="btn btn-primary btn-sm pull-right create showCreditModal"  data-toggle="modal" data-target="#credits">
                    <i class="fa fa-user-plus">إضافة</i>
                </button>
            @endpermission
        </div>
        <div class="box-body">
            <table id="units-table" class="table table-bordered table-hover text-center">
                <thead>
                    <tr>
                        <th>الشهر</th>
                        <th>التاريخ</th>
                        <th>النسبة</th>
                        <th>ملاحظات</th>
                        <th>الخيارات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($credits as $credit)
                        <tr>
                        <td>@if($credit->month == 1) 
                                     يناير
                                @elseif($credit->month == 2)
                                    فبراير
                                    @elseif($credit->month == 3)
                                    مارس
                                    @elseif($credit->month == 4)
                                    ابريل
                                    @elseif($credit->month == 5)
                                    مايو
                                    @elseif($credit->month == 6)
                                    يونيو
                                    @elseif($credit->month == 7)
                                    يوليو
                                    @elseif($credit->month == 8)
                                    اغسطس
                                    @elseif($credit->month == 9)
                                    سبتمر
                                    @elseif($credit->month == 10)
                                    اكتوبر
                                    @elseif($credit->month == 11)
                                    نوفمبر
                                @else
                                    ديسمبر

                                @endif
                            </td>
                            <td>{{ $credit->date }}</td>
                            <td>{{ $credit->bonus }}</td>
                            <td>{{ $credit->note }}</td>
                            <td>
                                {{-- @permission('units-read')
                                    <a class="btn btn-info btn-xs" href="{{ route('credits.show', $credit->id) }}"><i class="fa fa-eye"></i> عرض </a>
                                @endpermission --}}

                                @permission('credits-update')
                                    <a class="btn btn-warning btn-xs showCreditModal update" data-action="{{ route('credits.update', $credit->id) }}" data-id="{{ $credit->id }}" data-month="{{ $credit->month }}" data-date="{{ $credit->date }}" data-bonus="{{ $credit->bonus }}" data-note="{{ $credit->note }}"><i class="fa fa-edit"></i> تعديل </a>
                                @endpermission

                                @permission('credits-delete')
                                    <a class="btn btn-danger btn-xs" href="{{ route('credits.show', $credit->id) }}?delete=true"><i class="fa fa-trash"></i> حذف </a>
                                @endpermission
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <input type="hidden" data-action="{{ route('units.store') }}" id="create">
        </div>
    </div>
@endsection
