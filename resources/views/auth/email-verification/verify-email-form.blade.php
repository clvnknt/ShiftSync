@extends('layouts.app')

@section('title', 'Verify Email')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Verify Your Email Address') }}</div>

                <div class="card-body">
                    @if (session('message'))
                        <div class="alert alert-success" role="alert">
                            {{ session('message') }}
                        </div>
                    @endif

                    <p>{{ __('Before proceeding, please check your email for a verification link.') }}</p>
                    <p>{{ __('If you did not receive the email') }}, <a href="{{ route('verification.send') }}">{{ __('click here to request another') }}</a>.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
