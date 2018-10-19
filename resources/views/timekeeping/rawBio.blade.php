<table style="border:1px dotted #666; font-family:Arial,sans-serif; font-size:0.8em; font-weight:normal; width:50%">
  <tr>
    <th  style="border-bottom:3px solid #333">Production Date</td>
    <th  style="border-bottom:3px solid #333">Logs</th>
  </tr>
@foreach($record as $data)
<tr>
  <td  id="{{$data['id']}}" style="border-bottom:1px dotted #333" align="center">{{$data['Production Date']}}</td>
  <td style="border-bottom:1px dotted #333">@foreach($data['data'] as $d)
    <div style="border-bottom:1px solid #666">{{$d['Log Type']}} : <span style="float:right">{{$d['Log Time']}}</span></div><br/>
      @endforeach


  </td>
</tr>
@endforeach

</table>