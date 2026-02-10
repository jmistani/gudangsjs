<!DOCTYPE html>
<html>
<head>
    <link href="{{ asset('css/responsivetable.scss') }}" rel="stylesheet">
    <title>Daftar User</title>
</head>
<body>
<div class="table-users">
   <div class="header">Users</div>
    <h1>{{ $title }}</h1>
    <p>{{ $date }}</p>
    <table cellspacing="0">
       <tr>
         <td>Name</td>
         <td>Role</td>
         <td>Username</td>
       </tr>
      @foreach ($users as $user)
      <tr>
         <td>{{$user['name']}}</td>
         <td>
            <ul>
               @foreach ($user['roles'] as $role)
                  <li>{{$role->name}}</li>
               @endforeach
            </ul>
         </td>
         <td>{{$user['username']}}</td>
      </tr>
      @endforeach
    </table>
</div>
</body>
</html>