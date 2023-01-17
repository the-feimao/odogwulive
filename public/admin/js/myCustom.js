var base_url = $('#base_url').val();
var cur = $('#currency').val();
var pay_fd;

$(".preload").fadeOut(2000, function() {
    $("#app").fadeIn(700);        
});
$(document).ready(function() {    
    $(".select2").select2();
	
    
	

    $('#report_table').DataTable({
        dom: 'Bfrtip',
        dom: `<'row mb-2'<'col-sm-6 text-left'f><'col-sm-6 text-right'B>>
        <'row'<'col-sm-12'tr>>
        <'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 'lp>>`,
        
        language: {
            paginate: {
                previous: "<i class='fas fa-angle-left'>",
                next: "<i class='fas fa-angle-right'>"
            }
        },
        buttons: [{
            text: '<i class="fas fa-print"></i> Print',
            extend: 'print',
        }, 
        {
            text: '<i class="far fa-file-excel"></i> Excel',
            extend: 'excelHtml5',
            title: new Date().toISOString()
        },
        {
            text: '<i class="fas fa-file-csv"></i> CSV',
            extend: 'csvHtml5',
            title: new Date().toISOString()
        },
        {
            text: '<i class="far fa-file-pdf"></i> PDF',
            extend: 'pdfHtml5',
            title: new Date().toISOString()
        }],
        columnDefs: [ {
            orderable: true,
            className: 'select-checkbox',
            targets:   0
        } ],
        select: {
            style:    'os',
            selector: 'td:first-child'
        },
    });


    $('#revenue_table').DataTable({
        dom: 'Bfrtip',
        dom: `<'row mb-2'<'col-sm-6 text-left'f><'col-sm-6 text-right'B>>
        <'row'<'col-sm-12'tr>>
        <'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 'lp>>`,
        
        language: {
            paginate: {
                previous: "<i class='fas fa-angle-left'>",
                next: "<i class='fas fa-angle-right'>"
            }
        },
        buttons: [{
            text: '<i class="fas fa-print"></i> Print',
            extend: 'print',
        }, 
        {
            text: '<i class="far fa-file-excel"></i> Excel',
            extend: 'excelHtml5',
            title: new Date().toISOString()
        },
        {
            text: '<i class="fas fa-file-csv"></i> CSV',
            extend: 'csvHtml5',
            title: new Date().toISOString()
        },
        {
            text: '<i class="far fa-file-pdf"></i> PDF',
            extend: 'pdfHtml5',
            title: new Date().toISOString()
        }],
        columnDefs: [ {
            orderable: true,
            className: 'select-checkbox',
            targets:   0
        } ],
        select: {
            style:    'os',
            selector: 'td:first-child'
        },
        footerCallback: function (row, data, start, end, display) {
          
            var api = this.api();
            var tax_total = 0,tax=0,payment_total = 0,payment=0,com_total = 0,com=0,rev_total = 0,rev=0;
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(cur, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };
            api.columns(6).every(function () {
                tax_total = this.data()
                    .reduce(function (a, b) {
                        var x = intVal(a) || 0;
                        var y = intVal(b) || 0;
                        return x + y;
                    }, 0);                       
                
            });
            api.columns(6,
            {  page: 'current'}).every(function () {
                tax = this.data()
                    .reduce(function (a, b) {
                        var x = intVal(a) || 0;
                        var y = intVal(b) || 0;
                        return x + y;
                    }, 0);                                      
            });
            $(api.column( 6 ).footer()).html(cur+tax+'<br><br>'+cur+tax_total+'');


            api.columns(7).every(function () {
                payment_total  = this.data()
                        .reduce(function (a, b) {
                            var x = intVal(a) || 0;
                            var y = intVal(b) || 0;
                            return x + y;
                        }, 0);                                      
                });
            api.columns(7,{ page: 'current' }).every(function () {
                payment = this.data()
                    .reduce(function (a, b) {
                        var x = intVal(a) || 0;
                        var y = intVal(b) || 0;
                        return x + y;
                    }, 0);                                    
            });
            $(api.column( 7 ).footer()).html(cur+payment+'<br><br>'+cur+payment_total+'');

            api.columns(8).every(function () {
                com_total= this.data()
                    .reduce(function (a, b) {
                        var x = intVal(a) || 0;
                        var y = intVal(b) || 0;
                        return x + y;
                    }, 0);               
            });
            api.columns(8,{   page: 'current' }).every(function () {
                com = this.data()
                    .reduce(function (a, b) {
                        var x = intVal(a) || 0;
                        var y = intVal(b) || 0;
                        return x + y;
                    }, 0);               
            });
            $(api.column( 8 ).footer()).html(cur+com+'<br><br>'+cur+com_total+'');

            api.columns(9).every(function () {
                rev_total = this.data()
                    .reduce(function (a, b) {
                        var x = intVal(a) || 0;
                        var y = intVal(b) || 0;
                        return x + y;
                    }, 0);             
            });
            api.columns(9,{  page: 'current' }).every(function () {
                rev = this.data()
                    .reduce(function (a, b) {
                        var x = intVal(a) || 0;
                        var y = intVal(b) || 0;
                        return x + y;
                    }, 0);              
            });
            $(api.column( 9 ).footer()).html(cur+rev+'<br><br>'+cur+rev_total+'');
        }
    });

    
    $('#settlement_report').DataTable({
        dom: 'Bfrtip',
        dom: `<'row mb-2'<'col-sm-6 text-left'f><'col-sm-6 text-right'B>>
        <'row'<'col-sm-12'tr>>
        <'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 'lp>>`,
        
        language: {
            paginate: {
                previous: "<i class='fas fa-angle-left'>",
                next: "<i class='fas fa-angle-right'>"
            }
        },
        buttons: [{
            text: '<i class="fas fa-print"></i> Print',
            extend: 'print',
        }, 
        {
            text: '<i class="far fa-file-excel"></i> Excel',
            extend: 'excelHtml5',
            title: new Date().toISOString()
        },
        {
            text: '<i class="fas fa-file-csv"></i> CSV',
            extend: 'csvHtml5',
            title: new Date().toISOString()
        },
        {
            text: '<i class="far fa-file-pdf"></i> PDF',
            extend: 'pdfHtml5',
            title: new Date().toISOString()
        }],
        columnDefs: [ {
            orderable: true,
            className: 'select-checkbox',
            targets:   0
        } ],
        select: {
            style:    'os',
            selector: 'td:first-child'
        },
        footerCallback: function (row, data, start, end, display) {
          
            var api = this.api();
            var com_total = 0,com=0,paid_total = 0,paid=0,remain = 0,remain_total=0;
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(cur, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };
            api.columns(3).every(function () {
                com_total = this.data()
                    .reduce(function (a, b) {
                        var x = intVal(a) || 0;
                        var y = intVal(b) || 0;
                        return x + y;
                    }, 0);                                       
            });
            api.columns(3,{  page: 'current'}).every(function () {
                com = this.data()
                    .reduce(function (a, b) {
                        var x = intVal(a) || 0;
                        var y = intVal(b) || 0;
                        return x + y;
                    }, 0);                                      
            });
            $(api.column( 3 ).footer()).html(cur+com+'<br><br>'+cur+com_total+'');   
            
            api.columns(4).every(function () {
                paid_total = this.data()
                    .reduce(function (a, b) {
                        var x = intVal(a) || 0;
                        var y = intVal(b) || 0;
                        return x + y;
                    }, 0);                                       
            });
            api.columns(4,{  page: 'current'}).every(function () {
                paid = this.data()
                    .reduce(function (a, b) {
                        var x = intVal(a) || 0;
                        var y = intVal(b) || 0;
                        return x + y;
                    }, 0);                                      
            });
            $(api.column( 4 ).footer()).html(cur+paid+'<br><br>'+cur+paid_total+'');   

            api.columns(5).every(function () {
                remain_total = this.data()
                    .reduce(function (a, b) {
                        var x = intVal(a) || 0;
                        var y = intVal(b) || 0;
                        return x + y;
                    }, 0);                                       
            });
            api.columns(5,{  page: 'current'}).every(function () {
                remain = this.data()
                    .reduce(function (a, b) {
                        var x = intVal(a) || 0;
                        var y = intVal(b) || 0;
                        return x + y;
                    }, 0);                                      
            });
            $(api.column( 5 ).footer()).html(cur+remain+'<br><br>'+cur+remain_total+'');   
     
        }
    });

    $('#org-for-event').change(function() {		
        $.ajax({          
            type:"GET",
            url:base_url+'/getScanner/'+$(this).val(),
            success: function(result){
                $('.scanner_id').html('<option value="">Choose Scanner</option>');
                if(result.data.length>0){
                    result.data.forEach(e => {
                        $('.scanner_id').append('<option value="'+e.id+'">'+e.first_name+' '+e.last_name+'</option>');
                    });
                }                      
            },
            error: function(err){
              console.log('err ',err)
            }
        });
	});
    $("#role").change(function (e) {
        var vals = $(this).val();
        vals = JSON.stringify(vals);
        console.log(vals);
        if (vals.search("3") > 0) {
            $('#org').show();
        } else {
            $('#org').hide();
        }
     });
    

    $('#org_order_report').DataTable({
        dom: 'Bfrtip',
        dom: `<'row mb-2'<'col-sm-6 text-left'f><'col-sm-6 text-right'B>>
        <'row'<'col-sm-12'tr>>
        <'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 'lp>>`,
        
        language: {
            paginate: {
                previous: "<i class='fas fa-angle-left'>",
                next: "<i class='fas fa-angle-right'>"
            }
        },
        buttons: [{
            text: '<i class="fas fa-print"></i> Print',
            extend: 'print',
        }, 
        {
            text: '<i class="far fa-file-excel"></i> Excel',
            extend: 'excelHtml5',
            title: new Date().toISOString()
        },
        {
            text: '<i class="fas fa-file-csv"></i> CSV',
            extend: 'csvHtml5',
            title: new Date().toISOString()
        },
        {
            text: '<i class="far fa-file-pdf"></i> PDF',
            extend: 'pdfHtml5',
            title: new Date().toISOString()
        }],
        columnDefs: [ {
            orderable: true,
            className: 'select-checkbox',
            targets:   0
        } ],
        select: {
            style:    'os',
            selector: 'td:first-child'
        },
        footerCallback: function (row, data, start, end, display) {
          
            var api = this.api();
            var payment_total = 0,payment=0,tax_total = 0,tax=0,com = 0,com_total=0;
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(cur, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };
            api.columns(7).every(function () {
                payment_total = this.data()
                    .reduce(function (a, b) {
                        var x = intVal(a) || 0;
                        var y = intVal(b) || 0;
                        return x + y;
                    }, 0);                                       
            });
            api.columns(7,{  page: 'current'}).every(function () {
                payment = this.data()
                    .reduce(function (a, b) {
                        var x = intVal(a) || 0;
                        var y = intVal(b) || 0;
                        return x + y;
                    }, 0);                                      
            });
            $(api.column( 7 ).footer()).html(cur+payment+'<br><br>'+cur+payment_total+'');   
            
            api.columns(8).every(function () {
                tax_total = this.data()
                    .reduce(function (a, b) {
                        var x = intVal(a) || 0;
                        var y = intVal(b) || 0;
                        return x + y;
                    }, 0);                                       
            });
            api.columns(8,{  page: 'current'}).every(function () {
                tax = this.data()
                    .reduce(function (a, b) {
                        var x = intVal(a) || 0;
                        var y = intVal(b) || 0;
                        return x + y;
                    }, 0);                                      
            });
            $(api.column( 8 ).footer()).html(cur+tax+'<br><br>'+cur+tax_total+'');   
            
         
            api.columns(9).every(function () {
                com_total = this.data()
                    .reduce(function (a, b) {
                        var x = intVal(a) || 0;
                        var y = intVal(b) || 0;
                        return x + y;
                    }, 0);                                       
            });
            api.columns(9,{  page: 'current'}).every(function () {
                com = this.data()
                    .reduce(function (a, b) {
                        var x = intVal(a) || 0;
                        var y = intVal(b) || 0;
                        return x + y;
                    }, 0);                                      
            });
            $(api.column( 9 ).footer()).html(cur+com+'<br><br>'+cur+com_total+'');   
          
          
        }
    });
    

    $(document).on("click", ".btn-pay", function () {
        var user_id = $(this).data('id');
        var payment = $(this).data('payment');        
        $(".modal-body #user_id").val( user_id );
        $(".modal-body #payment").val( payment );
        pay_fd = new FormData();
        pay_fd.append('user_id',user_id); 
        pay_fd.append('payment',payment);        
   });
    if(document.getElementById('paypal-button-container')){        
        paypal_sdk.Buttons({
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: $('#payment').val()
                        },                   
                    }]
                });
            },         
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) { 
                    pay_fd.append('payment_status',1);  
                    pay_fd.append('payment_type','PAYPAL');  
                    pay_fd.append('payment_token',details.id);   
                    payToOrg();               
                });
            },
        
        }).render('#paypal-button-container');
    }
  

    $.uploadPreview({
        input_field: "#image-upload",
        preview_box: "#image-preview",
        label_field: "#image-label",
        label_default: "<i class='fas fa-plus'></i>",
        label_selected: "<i class='fas fa-plus'></i>",
        no_label: false,
        success_callback: null
      });
      
    $('#start_time,#end_time').flatpickr({
        minDate: "today",
        enableTime: true,        
        dateFormat: "Y-m-d h:i K",                
    });

    $('.duration').flatpickr({
        mode:'range',        
        dateFormat: "Y-m-d",                
    });
    if($('#eventDate').val()){
        $('#home_calender').flatpickr({        
            inline:true,    
            defaultDate: JSON.parse($('#eventDate').val()),
            dateFormat: "Y-m-d",  
            onMonthChange : function(selectedDates, dateStr, instance) {
                
               var month =  ("0" + (instance.currentMonth+1)).slice(-2);           
                $.ajax({ 
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },             
                    type:"POST",
                    url:base_url+'/get-month-event',
                    data:{
                        month:month,
                        year:instance.currentYear,
                    },
                    success: function(result){
                        $('.calender-event h5').html(moment(month, 'MM').format('MMMM')+' Events');
                        $('.home-upcoming-event').html('');
                        if(result.data.length == 0){
                            $('.home-upcoming-event').html('<div class="row"> <div class="col-12 text-center"> <div class="empty-data"><div class="card-icon shadow-primary"><i class="fas fa-search"></i> </div> <h6 class="mt-3">No events found </h6> </div></div></div>');  
                        }
                        else{
                            result.data.forEach(element => {
                            $('.home-upcoming-event').append('<div class="row mb-4"><div class="col-3"><div class="date-left"><h3 class="mb-0">'+element.date+'</h3><p class="mb-0">'+element.day+'</p></div></div><div class="col-9 event-right"><p class="mb-0 name">'+element.name+'</p><p class="mb-0">Ticket Sold <span>'+element.sold_ticket+'/'+element.tickets+'</span></p><div class="progress progress-sm mb-3" ><div class="progress-bar" id="progress-'+element.id+'" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div> </div></div></div>');      
                            $("#progress-"+element.id).css('width',element.average+'%');
                            });
                           
                        }
                    },
                    error: function(err){
                      console.log('err ',err)
                    }
                  });
    
            }            
        });
    }
   
    

    $('#start_date,#end_date').flatpickr({
        minDate: "today",             
        dateFormat: "Y-m-d",                
    });

    $('.textarea_editor').summernote({
        toolbar: [
          ['style', ['bold', 'italic', 'underline', 'clear']],
          ['font', ['strikethrough', 'superscript', 'subscript']],
          ['fontsize', ['fontsize']],
          ['color', ['color']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['height', ['height']]
        ]
      });

      $('.event-form input[type=radio][name=type]').change(function() {
        if(this.value=='online'){
            $('.location-detail').hide(500);
        }
        else if(this.value=='offline'){
            $('.location-detail').show(500);
        }
    });

    $('.ticket-form input[type=radio][name=type]').change(function() {
        if(this.value=='free'){
            $('.ticket-form #price').prop('disabled', true);
        }
        else if(this.value=='paid'){
            $('.ticket-form #price').prop('disabled', false);
        }
    });
    $(".inputtags").tagsinput('items');
    
    $('.event-form').on('keyup keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) { 
          e.preventDefault();
          return false;
        }
    });

    $(".colorpickerinput").colorpicker({
        format: 'hex',
        component: '.input-group-append',
    });

});

function notificationDetail(id){
    $.ajax({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type:"GET",
        url:base_url+'/notification-template/'+id+'/edit',
        success: function(result){
          $("#edit-template-form input[name='subject']").val(result.data.subject);
          $('#edit-template-form').get(0).setAttribute('action', base_url+'/notification-template/'+result.data.id);
          $('.textarea_editor').summernote('code',result.data.mail_content);
          $("#edit-template-form textarea#message_content").html(result.data.message_content);
        },
        error: function(err){
          console.log('err ',err)
        }
      });
}

function addRate(id){
    $('.rating i').css('color','#d2d2d2');
    $('.feedback-form input[name="rate"]').val(id);
    for (let i = 1; i <= id; i++) {
      $('.rating #rate-'+i).css('color','#fec009');
    }
}

function deleteData(url, id){

    Swal.fire({
        title: 'Are you sure?',
        text: "You won't to delete this record and all connected data with this record",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete all'
    }).then((result) => {
    if (result.value) {

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: "DELETE",
            dataType: "JSON",
            url: base_url+'/'+url + '/' + id,
            success: function (result) {
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
                Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: 'Record is deleted successfully.'
                })
            },
            error: function (err) {
                console.log('err ', err)
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'This record is connect with another data!'
                })
            }
        });
    }
    })

}

function getStatistics(number){
    var month =  ("0" + (number)).slice(-2);     
    $.ajax({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type:"GET",
        url:base_url+'/getStatistics/'+month,
        success: function(result){
          $('.card-stats .order-pending').html(result.data.pending_order);
          $('.card-stats .order-complete').html(result.data.complete_order);
          $('.card-stats .order-cancel').html(result.data.cancel_order);
          $('.card-statistic-2 .order-total').html(result.data.total_order);
        },
        error: function(err){
          console.log('err ',err)
        }
    });
}

function payLocally(){
    pay_fd.append('payment_type',"LOCAL");
    pay_fd.append('payment_status',1);
    payToOrg(); 
}

function payToOrg(){
    $.ajax({ 
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },             
        type:"POST",
        url:base_url+'/pay-to-organization',
        data:pay_fd,
        cache:false,
        contentType: false,
        processData: false,
        success: function(result){
            if(result.success==true){
                Swal.fire({
                    icon: 'success',
                    title: 'Payment done Successfully!',                    
                }) 
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }         
        },
        error: function(err){
          console.log('err ',err)
        }
      });
}

function changeOrderStatus(id) {
    var con = "#status-"+id;
    var order_status = $(con).val();
    $.ajax({
        url: 'order/changestatus',
        method: 'post',
        data: {id: id,order_status: order_status, _token: $('meta[name="csrf-token"]').attr('content')},
        success: function(res) {
            if (order_status == 'Complete' || order_status == 'Cancel') {
                $(con).prop("disabled", true);
            }
        },
        error: function(error) {}
    });
}

function changePaymentStatus(id) {
    var con = "#payment-"+id;
    var payment_status = $(con).val();
    $.ajax({
        url: 'order/changepaymentstatus',
        method: 'post',
        data: {id: id,payment_status: payment_status, _token: $('meta[name="csrf-token"]').attr('content')},
        success: function(res) {
            if (payment_status == 1) {
                $(con).prop("disabled", true);
            }
        },
        error: function(error) {}
    });
}