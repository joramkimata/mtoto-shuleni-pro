@extends('layout')

@section('title', 'Massages')

@section('main')

<div class="row">

	<div class="col-md-12">
		<div class="x_panel">
                  <div class="x_title">
                    <h2><i class="fa fa-list"></i> Messages</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    @if (Session::has('error_message'))
                          <div class="alert alert-danger" role="alert">
                              {{Session::get('error_message')}}
                          </div>
                      @endif
                      @if($threads->count() > 0)
                          @foreach($threads as $thread)
                          <?php $class = $thread->isUnread($currentUserId) ? 'alert-info' : ''; ?>
                          <div class="media alert {{$class}}">
                              <h4 class="media-heading">{{link_to('messages/' . $thread->id, $thread->subject)}}</h4>
                              <p>{{$thread->latestMessage->body}}</p>
                              <p><small><strong>Creator:</strong> {{ $thread->creator()->name }}</small></p>
                              <p><small><strong>Participants:</strong> {{ $thread->participantsString(Auth::id()) }}</small></p>
                          </div>
                          @endforeach
                      @else
                          <div class="alert alert-danger" role="alert">Sorry, no threads.</div>
                      @endif
                  </div>
                  </div>
            </div>

                  </div>

@stop
