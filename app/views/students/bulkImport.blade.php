<form id="importForm">
<div class="row" id="importer">
    <div class="col-md-12">
        <div class="form-group">
            <p>Import Excel File Contains Students Info<br/><br/>
              <label id="file_" style="cursor: pointer" class="btn btn-warning"><i class="fa fa-file"></i> Import Excel File</label>
              <input type="file" style="display:none" id="bulkImport"  name="bulkImport" />​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​
            </p>
            <hr/>
            <div id="loader_are" style="display: none">
                <img src="{{url('images/ijax.gif')}}" />
            </div>
        </div>
  </div>
</div>
</form>

<h4><i class="fa fa-info-circle"></i> Note: Please make sure the <i><b>excel</b></i> looks like the picture below and parent name should be already registered</h4>
<hr/>
<img src="{{url('images/excel.png')}}" width="80%" />
<hr/>
Powered By Izweb Technologies &copy;{{date('Y')}}


<script src="{{url('vendors/jquery/dist/jquery.min.js')}}"></script>
<script type="text/javascript" src="{{url('ajaxform/af.js')}}"></script>
<script type="text/javascript" src="{{url('iztools/biggo.js')}}"></script>

<script>
    $(document).ready(function() {

      var wrapper = $('<div/>').css({height:0,width:0,'overflow':'hidden'});
      var fileInput = $('#bulkImport').wrap(wrapper);


          fileInput.change(function(){

           var file = (fileInput[0].files[0]);

           var arr  = Biggo.serializeData(importForm);
           var arr2 = ["bulkImport"];
           var isFileUpload = true;
           var dataX = Biggo.prepareFormData(arr, arr2);



          var imageType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

          if (file.type.match(imageType)) {

                $('#file_').css('cursor', 'wait');
                $('#importer').css('cursor', 'wait').css('opacity', 0.2);
                $('#loader_are').show();


                var biggo = Biggo.talkToServer('{{route('students.bulkImport')}}', dataX, isFileUpload,'post', 'text', null).then(function(res){
                        $('#file_').css('cursor', 'pointer');
                        $('#importer').css('cursor', 'pointer').css('opacity', 1);
                        $('#loader_are').hide();
                        var dt = JSON.parse(res);
                        if(!dt.error){
                            Biggo.showFeedBack(file_, dt.msg, dt.error);
                        }else{
                            Biggo.showFeedBack(file_, dt.msg, dt.error);
                        }

                });

                biggo.fail(function(err){
                    $('#file_').css('cursor', 'pointer');
                    $('#importer').css('cursor', 'pointer');
                    $('#loader_are').hide();
                    var error = JSON.stringify(err);
                    Biggo.errorBox(file_, error);
                });



          } else {
            Biggo.showFeedBack(importer, 'Please Upload Excel File', true);
            return false;
          }

      });

      $('#file_').click(function(){
          fileInput.click();
      }).show();


    });
  </script>