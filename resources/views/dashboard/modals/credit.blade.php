<div class="modal fade" id="creditModal" tabindex="-1" role="dialog" aria-labelledby="creditsLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="creditsLabel">إضافة عمولة</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="form_credits" action="{{ route('credits.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>الشهر</label>
			<select name="month" class="form-control month" id="">
						<option value="1">يناير</option>
						<option value="2">فبراير</option>
						<option value="3">مارس</option>
						<option value="4">ابريل</option>
						<option value="5">مايو</option>
						<option value="6">يونيو</option>
						<option value="7">يوليو</option>
						<option value="8">اغسطس</option>
						<option value="9">سبتمر</option>
						<option value="10">اكتوبر</option>
						<option value="11">نوفمبر</option>
						<option value="12">ديسمبر</option>
					</select>
                </div>
				<div class="form-group">
				<input  class="form-control date" autocomplete="off" type="date" name="date" placeholder="التاريخ">
				</div>
				<div class="form-group">
				<input  class="form-control bonus" autocomplete="off" type="number" step=".01" name="bonus" placeholder="النسبة">
				</div>
				<div class="form-group">
					<textarea name="note" class="form-control note" id="" cols="80" placeholder="ملاحظات" rows="10"></textarea>
				</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                <button type="submit" class="btn btn-primary">حفظ</button>
            </div>
        </form>
      </div>
    </div>
  </div>

<script>
  $('.showCreditModal').click(function() {
    if($(this).hasClass("update")){
      $('#form_credits').attr('action', $(this).data('action'))
      $('#form_credits').append('<input type="hidden" name="_method" value="PUT">')

      //set fields data
      $('#creditModal input[name="month"]').val($(this).data('month'))
	  $('#creditModal input[name="date"]').val($(this).data('date'))
	  $('#creditModal input[name="bonus"]').val($(this).data('bonus'))
	  $('#creditModal textarea[name="note"]').val($(this).data('note'))
    }
    else{
      $('#form_credits').attr('action', "{{ route('credits.store') }}")
      $('#form_credits').remove('input[name="_method"]')

      //Clear from fields
      $('#creditModal input[name="month"]').val('')
	  $('#creditModal input[name="date"]').val('')
	  $('#creditModal input[name="bonus"]').val('')
	  $('#creditModal textarea[name="note"]').val('')
    }
    
    $('#creditModal').modal('show')
  })
</script>