<div class="modal fade" id="myModal{{$modelID}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        
          <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
          <h4 class="modal-title text-success" id="myModalLabel"><i class="fa fa-calendar"></i> {{$modalTitle}}</h4>
        
      </div> 
      <div class="modal-body-upload" style="padding:20px;">
       

        <br/><br/>
         <div class="row">

             
              <div class="col-sm-12">

                <div class="row">
                  
                  <div class="col-sm-3"><h5 class="text-primary">Production Date</h5></div>
                  <div class="col-sm-6"><h5 class="text-primary">Work Shift</h5></div>
                  <div class="col-sm-3"><h5 class="text-primary">Status</h5></div>
                </div>

                 <div class="row">
                  
                  <div class="col-sm-3"><?php echo date('M d, Y - l', strtotime($data['productionDate'])) ?></div>
                  <div class="col-sm-6">
                    <strong>From:  &nbsp;</strong> {{date('h:i A', strtotime($data['usercws']['timeStart_old'])) }} - {{ date('h:i A', strtotime($data['usercws']['timeEnd_old'])) }}<br/>
                    <strong>To: &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;</strong>  {{date('h:i A', strtotime($data['usercws']['timeStart'])) }} - {{ date('h:i A', strtotime($data['usercws']['timeEnd'])) }}</div>
                  <div class="col-sm-3">
                    @if (is_null($data['usercws']['isApproved']))
                    <h4 class="text-gray"><em>For Approval</em></h4>
                    @else
                        @if ($data['usercws']['isApproved']) 
                        <h4 class="text-success">Approved</h4>
                        @else 
                        <h4 class="text-danger">Denied</h4>@endif
                    @endif


                  </div>
                </div>

               
              </div>

               
               
         </div>

         

        
        
        
        
        <button type="button" class="btn btn-default btn-md pull-right" data-dismiss="modal"style="margin-right:5px; margin-top:50px" > <i class="fa fa-check" ></i> Okay </button>

     
      </div> 
      <div class="modal-footer no-border">
        
      </div>
    </div>
  </div>
</div>