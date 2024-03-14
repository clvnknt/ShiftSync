<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <!-- Navigation links -->
        <div class="nav-links">
            <img src="{{ asset('media/images/cloudstaff-logo-share.png') }}" alt="CloudStaff Logo" height="25px" style="margin-right: 10px;">
            <a href="{{ route('timesheet') }}">Dashboard</a>
            <a href="{{ route('timesheet') }}">Timesheet</a>
            <a href="#">Support</a>
            <a href="#">My Account</a>
        </div>
        <!-- Dropdown menu for user account -->
        <div class="dropdown">
            <button class="dropbtn">Welcome, {{ Auth::user()->name }}</button>
            <div class="dropdown-content">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <h1>Dashboard</h1>

    <div class="dashboard-container">
        <!-- User information section -->
        <div class="user-info-container">
            <h2>User Information</h2>
            @if($employeeRecord)
                <p>Full Name: {{ $employeeRecord->first_name }} {{ $employeeRecord->middle_name }} {{ $employeeRecord->last_name }}</p>
            @else
                <p>Employee record not found.</p>
            @endif
            <p>Email: {{ Auth::user()->email }}</p>
            <p>Timezone: {{ Auth::user()->timezone }}</p>
            <p>Default Shift: {{ $employeeRecord->defaultShift ? $employeeRecord->defaultShift->shift_name : 'N/A' }}</p>
            <p>Start Time: {{ $employeeRecord->defaultShift ? $employeeRecord->defaultShift->shift_start_time : 'N/A' }}</p>
            <p>End Time: {{ $employeeRecord->defaultShift ? $employeeRecord->defaultShift->shift_end_time : 'N/A' }}</p>
        </div>

        <!-- Today's shift section -->
        <div class="shifts-container">
            <h2>Today's Shift: {{ $employeeShift && $employeeShift->shift_date ? \Illuminate\Support\Carbon::parse($employeeShift->shift_date)->format('d/m/Y') : 'N/A' }}</h2>
            <table class="shifts-table">
                <tbody>
                    <!-- Shift Started section -->
                    <tr>
                        <th>Shift Started</th>
                        <td>
                            @if ($employeeShift && $employeeShift->shift_started)
                                {{ $employeeShift->shift_started->format('H:i') }}
                            @else
                                <form action="{{ route('startShift') }}" method="POST">
                                    @csrf
                                    <button type="submit">START SHIFT</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    <!-- Lunch Started section -->
                    <tr>
                        <th>Lunch Started</th>
                        <td>
                            @if ($employeeShift && $employeeShift->lunch_started)
                                {{ $employeeShift->lunch_started->format('H:i') }}
                            @elseif ($employeeShift && $employeeShift->shift_started && !$employeeShift->shift_ended)
                                <form action="{{ route('startLunch') }}" method="POST">
                                    @csrf
                                    <button type="submit">START LUNCH</button>
                                </form>
                            @else
                                <button type="button" disabled>START LUNCH</button>
                            @endif
                        </td>
                    </tr>
                    <!-- Lunch Ended section -->
                    <tr>
                        <th>Lunch Ended</th>
                        <td>
                            @if ($employeeShift && $employeeShift->lunch_ended)
                                {{ $employeeShift->lunch_ended->format('H:i') }}
                            @elseif ($employeeShift && $employeeShift->lunch_started)
                                <form action="{{ route('endLunch') }}" method="POST">
                                    @csrf
                                    <button type="submit">END LUNCH</button>
                                </form>
                            @else
                                <button type="button" disabled>END LUNCH</button>
                            @endif
                        </td>
                    </tr>
                    <!-- Shift Ended section -->
                    <tr>
                        <th>Shift Ended</th>
                        <td>
                            @if ($employeeShift && $employeeShift->shift_started && $employeeShift->lunch_started && $employeeShift->lunch_ended && !$employeeShift->shift_ended)
                                <form action="{{ route('endShift') }}" method="POST">
                                    @csrf
                                    <button type="submit">END SHIFT</button>
                                </form>
                            @elseif ($employeeShift && $employeeShift->shift_ended)
                                {{ $employeeShift->shift_ended->format('H:i') }}
                            @else
                                <button type="button" disabled>END SHIFT</button>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
