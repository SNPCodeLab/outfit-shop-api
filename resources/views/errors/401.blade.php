@extends('errors.layout')

@section('title', '401 Unauthorized')
@section('code', '401')
@section('message', 'Authentication Required')
@section('description', 'A valid Bearer token is required to access this endpoint. Please login via /api/v1/auth/login.')
