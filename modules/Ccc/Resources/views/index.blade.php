@extends('ccc::layouts.master')
<?php $classes = ['info', 'active']; ?>
@section('content_left')
  <div class="row">
    @foreach($invoice_links as $year => $years)
      <div class="col-md-6 ui-sortable">
        <div class="panel panel-inverse card-2 d-flex flex-column">
          <div class="panel-heading d-flex flex-row justify-content-between">
            <h4 class="panel-title d-flex">
              <span data-click="panel-collapse">
                {{ trans('messages.Invoices') }} {{$year}}
              </span>
            </h4>
            <div class="panel-heading-btn d-flex flex-row">
              <a href="javascript:;"
                 class="btn btn-xs btn-icon btn-circle btn-warning d-flex"
                 data-click="panel-collapse"
                 style="justify-content: flex-end;align-items: center">
                <i class="fa fa-minus"></i>
              </a>
            </div>
          </div>
          <div class="panel-body fader d-flex flex-column" style="overflow-y:auto;@if($loop->first)@else display: none;@endif; height:100%">
            <table class="table table-bordered">
              @foreach($years as $month => $types)
                <?php
                  if (! is_int($month)) {
                    continue;
                  }
                  $bsclass = $classes[$month % 2];
                ?>
                <tr class="{{$bsclass}}">
                  <!-- Invoice(s) -->
                  <td class="" align="center">
                  @if(isset($types['INVOICE']))
                    @foreach($types['INVOICE'] as $i => $invoice)
                      <i class="fa fa-id-card-o"></i>&nbsp; {{ $invoice }}
                      @if(isset($types['INVOICE'][$i+1]))
                        &emsp; | &emsp;
                      @endif
                    @endforeach
                  @endif
                  </td>
                  <!-- CDR -->
                  @if($years['formatting']['cdr'])
                    <td class="" align="center" style="width: 50%">
                      @if(isset($types['CDR'][0]))
                        <i class="fa fa-id-card-o"></i>&nbsp; {{ $types['CDR'][0] }}
                      @else
                        -
                      @endif
                    </td>
                  @endif
                </tr>
              @endforeach
            </table>
          </div>
        </div>
      </div>
    @endforeach
  </div>

 <div class="row">
      <div class="col-md-6 ui-sortable">
        <div class="panel panel-inverse card-2 d-flex flex-column">
          <div class="panel-heading d-flex flex-row justify-content-between">
            <h4 class="panel-title d-flex">
              <span data-click="panel-collapse" data-original-title="" title="" data-init="true">Speed Test </span>
              <span data-click="panel-collapse">
            </h4>
            <div class="panel-heading-btn d-flex flex-row">
              <a href="javascript:;"
                 class="btn btn-xs btn-icon btn-circle btn-warning d-flex"
                 data-click="panel-collapse"
                 style="justify-content: flex-end;align-items: center">
                <i class="fa fa-minus"></i>
              </a>
            </div>
          </div>
          <div class="panel-body fader d-flex flex-column" style="overflow-y:auto; height:100%">
          <script type="text/javascript" src="speedtest.js"></script>
          <script type="text/javascript">
          function I(i){return document.getElementById(i);}
          //INITIALIZE SPEEDTEST
          var s=new Speedtest(); //create speedtest object
          s.setParameter("url_dl","customer/speedtest/garbage")
          s.setParameter("url_ul","backend/empty.php")
          s.setParameter("url_ping","backend/empty.php")
          s.setParameter("telemetry_level","basic"); //enable telemetry

          var meterBk=/Trident.*rv:(\d+\.\d+)/i.test(navigator.userAgent)?"#EAEAEA":"#80808040";
          var dlColor="#6060AA",
            ulColor="#309030",
            pingColor="#AA6060",
            jitColor="#AA6060";
          var progColor=meterBk;

          //CODE FOR GAUGES
          function drawMeter(c,amount,bk,fg,progress,prog){
            var ctx=c.getContext("2d");
            var dp=window.devicePixelRatio||1;
            var cw=c.clientWidth*dp, ch=c.clientHeight*dp;
            var sizScale=ch*0.0055;
            if(c.width==cw&&c.height==ch){
              ctx.clearRect(0,0,cw,ch);
            }else{
              c.width=cw;
              c.height=ch;
            }
            ctx.beginPath();
            ctx.strokeStyle=bk;
            ctx.lineWidth=12*sizScale;
            ctx.arc(c.width/2,c.height-58*sizScale,c.height/1.8-ctx.lineWidth,-Math.PI*1.1,Math.PI*0.1);
            ctx.stroke();
            ctx.beginPath();
            ctx.strokeStyle=fg;
            ctx.lineWidth=12*sizScale;
            ctx.arc(c.width/2,c.height-58*sizScale,c.height/1.8-ctx.lineWidth,-Math.PI*1.1,amount*Math.PI*1.2-Math.PI*1.1);
            ctx.stroke();
            if(typeof progress !== "undefined"){
              ctx.fillStyle=prog;
              ctx.fillRect(c.width*0.3,c.height-16*sizScale,c.width*0.4*progress,4*sizScale);
            }
          }
          function mbpsToAmount(s){
            return 1-(1/(Math.pow(1.3,Math.sqrt(s))));
          }
          function msToAmount(s){
            return 1-(1/(Math.pow(1.08,Math.sqrt(s))));
          }
          //UI CODE
          var uiData=null;
          function startStop(){
              if(s.getState()==3){
              //speedtest is running, abort
              s.abort();
              data=null;
              I("startStopBtn").className="";
              initUI();
            }else{
              //test is not running, begin
              I("startStopBtn").className="running";
              I("shareArea").style.display="none";
              s.onupdate=function(data){
                      uiData=data;
              };
              s.onend=function(aborted){
                      I("startStopBtn").className="";
                      updateUI(true);
                      if(!aborted){
                          //if testId is present, show sharing panel, otherwise do nothing
                          try{
                              var testId=uiData.testId;
                              if(testId!=null){
                                  var shareURL=window.location.href.substring(0,window.location.href.lastIndexOf("/"))+"/results/?id="+testId;
                                  I("resultsImg").src=shareURL;
                                  I("resultsURL").value=shareURL;
                                  I("testId").innerHTML=testId;
                                  I("shareArea").style.display="";
                              }
                          }catch(e){}
                      }
              };
              s.start();
            }
          }
          //this function reads the data sent back by the test and updates the UI
          function updateUI(forced){
            if(!forced&&s.getState()!=3) return;
            if(uiData==null) return;
            var status=uiData.testState;
            I("ip").textContent=uiData.clientIp;
            I("dlText").textContent=(status==1&&uiData.dlStatus==0)?"...":uiData.dlStatus;
            drawMeter(I("dlMeter"),mbpsToAmount(Number(uiData.dlStatus*(status==1?oscillate():1))),meterBk,dlColor,Number(uiData.dlProgress),progColor);
            I("ulText").textContent=(status==3&&uiData.ulStatus==0)?"...":uiData.ulStatus;
            drawMeter(I("ulMeter"),mbpsToAmount(Number(uiData.ulStatus*(status==3?oscillate():1))),meterBk,ulColor,Number(uiData.ulProgress),progColor);
            I("pingText").textContent=uiData.pingStatus;
            drawMeter(I("pingMeter"),msToAmount(Number(uiData.pingStatus*(status==2?oscillate():1))),meterBk,pingColor,Number(uiData.pingProgress),progColor);
            I("jitText").textContent=uiData.jitterStatus;
            drawMeter(I("jitMeter"),msToAmount(Number(uiData.jitterStatus*(status==2?oscillate():1))),meterBk,jitColor,Number(uiData.pingProgress),progColor);
          }
          function oscillate(){
            return 1+0.02*Math.sin(Date.now()/100);
          }
          //update the UI every frame
          window.requestAnimationFrame=window.requestAnimationFrame||window.webkitRequestAnimationFrame||window.mozRequestAnimationFrame||window.msRequestAnimationFrame||(function(callback,element){setTimeout(callback,1000/60);});
          function frame(){
            requestAnimationFrame(frame);
            updateUI();
          }
          frame(); //start frame loop
          //function to (re)initialize UI
          function initUI(){
            drawMeter(I("dlMeter"),0,meterBk,dlColor,0);
            drawMeter(I("ulMeter"),0,meterBk,ulColor,0);
            drawMeter(I("pingMeter"),0,meterBk,pingColor,0);
            drawMeter(I("jitMeter"),0,meterBk,jitColor,0);
            I("dlText").textContent="";
            I("ulText").textContent="";
            I("pingText").textContent="";
            I("jitText").textContent="";
            I("ip").textContent="";
          }
          </script>
          <style type="text/css">
            html,body{
              border:none; padding:0; margin:0;
              background:#FFFFFF;
              color:#202020;
            }
            body{
              text-align:center;
              font-family:"Roboto",sans-serif;
            }
            h1{
              color:#404040;
            }
            #startStopBtn{
              display:inline-block;
              margin:0 auto;
              color:#6060AA;
              background-color:rgba(0,0,0,0);
              border:0.15em solid #6060FF;
              border-radius:0.3em;
              transition:all 0.3s;
              box-sizing:border-box;
              width:8em; height:3em;
              line-height:2.7em;
              cursor:pointer;
              box-shadow: 0 0 0 rgba(0,0,0,0.1), inset 0 0 0 rgba(0,0,0,0.1);
            }
            #startStopBtn:hover{
              box-shadow: 0 0 2em rgba(0,0,0,0.1), inset 0 0 1em rgba(0,0,0,0.1);
            }
            #startStopBtn.running{
              background-color:#FF3030;
              border-color:#FF6060;
              color:#FFFFFF;
            }
            #startStopBtn:before{
              content:"Start";
            }
            #startStopBtn.running:before{
              content:"Abort";
            }
            #test{
              margin-top:2em;
              margin-bottom:2em;
            }
            div.testArea{
              display:inline-block;
              width:16em;
              height:12.5em;
              position:relative;
              box-sizing:border-box;
            }
            div.testName{
              position:absolute;
              top:0.1em; left:0;
              width:100%;
              font-size:1.4em;
              z-index:9;
            }
            div.meterText{
              position:absolute;
              bottom:1.55em; left:0;
              width:100%;
              font-size:2.5em;
              z-index:9;
            }
            div.meterText:empty:before{
              content:"0.00";
            }
            div.unit{
              position:absolute;
              bottom:2em; left:0;
              width:100%;
              z-index:9;
            }
            div.testArea canvas{
              position:absolute;
              top:0; left:0; width:100%; height:100%;
              z-index:1;
            }
            div.testGroup{
              display:inline-block;
            }
            #shareArea{
              width:95%;
              max-width:40em;
              margin:0 auto;
              margin-top:2em;
            }
            #shareArea > *{
              display:block;
              width:100%;
              height:auto;
              margin: 0.25em 0;
            }
            @media all and (max-width:65em){
              body{
                font-size:1.5vw;
              }
            }
            @media all and (max-width:40em){
              body{
                font-size:0.8em;
              }
              div.testGroup{
                display:block;
                margin: 0 auto;
              }
            }
          </style>
          <title>HTML5 Speedtest</title>
          </head>
          <body>
            <div id="testWrapper">
             <div id="test">
              <div class="testGroup">
                <div class="testArea">
                  <div class="testName">Download</div>
                  <canvas id="dlMeter" class="meter"></canvas>
                  <div id="dlText" class="meterText"></div>
                  <div class="unit">Mbps</div>
                </div>
                <div class="testArea">
                  <div class="testName">Upload</div>
                  <canvas id="ulMeter" class="meter"></canvas>
                  <div id="ulText" class="meterText"></div>
                  <div class="unit">Mbps</div>
                </div>
              </div>
              <div class="testGroup">
                <div class="testArea">
                  <div class="testName">Ping</div>
                  <canvas id="pingMeter" class="meter"></canvas>
                  <div id="pingText" class="meterText"></div>
                  <div class="unit">ms</div>
                </div>
                <div class="testArea">
                  <div class="testName">Jitter</div>
                  <canvas id="jitMeter" class="meter"></canvas>
                  <div id="jitText" class="meterText"></div>
                  <div class="unit">ms</div>
                </div>
              </div>
              <div id="ipArea">
                IP Address: <span id="ip"></span>
              </div>
                <div id="startStopBtn" onclick="startStop()"></div>
              <div id="shareArea" style="display:none">

              </div>
          </div>
      </div>
  </div>
</div>

@stop

@section('content_emails')

  <table class="table">
    @foreach($emails as $email)
      <tr><td> {!! HTML::linkRoute('CustomerPsw', $email->view_index_label()['header'], ['email_id' => $email->id]) !!} </td><td>{{ $email->get_type() }}</td></tr>
    @endforeach
  </table>

@stop

@section('content')

  @include ('bootstrap.blank', array ('content' => 'content_left', 'invoice_links' => $invoice_links, 'view_header' => trans('messages.Invoices'), 'md' => 8))

  @if (!$emails->isEmpty())
    @include ('bootstrap.panel', array ('content' => 'content_emails', 'emails' => $emails, 'view_header' => App\Http\Controllers\BaseViewController::translate_label('E-Mail Address'), 'md' => 4))
  @endif

@stop



