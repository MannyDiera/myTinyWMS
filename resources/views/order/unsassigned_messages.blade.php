@extends('layout.app')

@section('title', 'Neue Nachrichten')

@section('breadcrumb')
    <li>
        <a href="{{ route('order.index') }}">Bestellungen</a>
    </li>
    <li class="active">
        <strong>Neue Nachrichten</strong>
    </li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>
                        Nicht zugeordnete neue Nachrichten
                    </h5>
                </div>
                <div class="ibox-content">
                    <div class="fh-breadcrumb">
                        <div class="fh-column">
                            <div class="slimScrollDiv" style="position: relative; overflow: hidden; width: auto; height: 100%;">
                                <div class="full-height-scroll" style="overflow: hidden; width: auto; height: 100%;">
                                    <ul class="list-group elements-list">
                                        @foreach($unassignedMessages as $message)
                                            <li class="list-group-item">
                                                <a data-toggle="tab" href="#tab-{{ $loop->iteration }}">
                                                    <small class="pull-right text-muted" title="{{ $message->received->format('d.m.Y H:i:s') }}"> {{ $message->received->format('d.m.Y') }}</small>
                                                    <strong title="{{ optional($message->user)->name }}">{{ $message->sender->contains('System') ? 'System' : 'Lieferant' }}</strong>
                                                    <div class="small m-t-xs">
                                                        <p class="m-b-xs">{{ $message->subject }}</p>
                                                    </div>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="slimScrollBar" style="background: rgb(0, 0, 0) none repeat scroll 0 0; width: 7px; position: absolute; top: 0; opacity: 0.4; display: none; border-radius: 7px; z-index: 99; right: 1px; height: 536.965px;"></div>
                                <div class="slimScrollRail" style="width: 7px; height: 100%; position: absolute; top: 0px; display: none; border-radius: 7px; background: rgb(51, 51, 51) none repeat scroll 0% 0%; opacity: 0.2; z-index: 90; right: 1px;"></div>
                            </div>
                        </div>

                        <div class="full-height">
                            <div class="slimScrollDiv" style="position: relative; overflow: hidden; width: auto; height: 100%;">
                                <div class="full-height-scroll white-bg border-left" style="overflow: hidden; width: auto; height: 100%;">
                                    <div class="element-detail-box">
                                        <div class="tab-content">
                                            @foreach($unassignedMessages as $message)
                                                <div id="tab-{{ $loop->iteration }}" class="tab-pane @if($loop->first) active @endif">
                                                    <div class="pull-right">
                                                        <div class="tooltip-demo">
                                                            {{--<a href="{{ route('order.message_create', ['order' => $order, 'answer' => $message->id]) }}" class="btn btn-white btn-xs" data-toggle="tooltip" data-placement="bottom" title="auf Nachricht antworten"><i class="fa fa-reply"></i> Antworten</a>
                                                            @if(!$message->read)
                                                                <a href="{{ route('order.message_read', [$order, $message]) }}" class="btn btn-white btn-xs" title="Als Gelesen markieren"><i class="fa fa-eye"></i> Gelesen</a>
                                                            @else
                                                                <a href="{{ route('order.message_unread', [$order, $message]) }}" class="btn btn-white btn-xs" title="Als Ungelesen markieren"><i class="fa fa-eye"></i> Ungelesen</a>
                                                            @endif
                                                            --}}{{--<button class="btn btn-white btn-xs" data-toggle="tooltip" data-placement="top" title="" data-original-title="Mark as important"><i class="fa fa-exclamation"></i> </button>--}}{{--
                                                            <form action="{{ route('order.message_delete', [$order, $message]) }}" class="list-form" method="POST">
                                                                {{ csrf_field() }}
                                                                <button class="btn btn-white btn-xs" onclick="return confirm('Wirklich löschen?')" title="Nachricht löschen"><i class="fa fa-trash-o"></i> Löschen</button>
                                                            </form>--}}
                                                        </div>
                                                    </div>
                                                    <div class="small text-muted">
                                                        <i class="fa fa-clock-o"></i> {{ $message->received->formatLocalized('%A, %d.%B %Y, %H:%M Uhr') }}
                                                        @if ($message->sender->contains('System'))
                                                            von {{ $message->user ? $message->user->name : 'System' }} an {{ $message->receiver->implode(', ') }}
                                                        @else
                                                            von {{ $message->sender->implode(', ') }}
                                                        @endif
                                                    </div>

                                                    <h1>{{ $message->subject }}</h1>

                                                    <iframe seamless frameborder="0" class="full-width" srcdoc='{!! $message->htmlBody  !!}'></iframe>

                                                    @if($message->attachments->count())
                                                        <div class="m-t-lg">
                                                            <p>
                                                                <span><i class="fa fa-paperclip"></i> {{ $message->attachments->count() }} {{ trans_choice('plural.attachment', $message->attachments->count()) }}{{-- - --}}</span>
                                                                {{--<a href="#">Download all</a>
                                                                |
                                                                <a href="#">View all images</a>--}}
                                                            </p>

                                                            <div class="attachment">
                                                                @foreach($message->attachments as $attachment)
                                                                    <div class="file-box">
                                                                        <div class="file">
                                                                            <a href="{{ route('order.message_attachment_download', [$message->id, $attachment['fileName']]) }}">
                                                                                <span class="corner"></span>

                                                                                <div class="icon">
                                                                                    <i class="fa fa-file"></i>
                                                                                </div>
                                                                                <div class="file-name">
                                                                                    {{ $attachment['orgFileName'] }}
                                                                                </div>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                                <div class="clearfix"></div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="slimScrollBar" style="background: rgb(0, 0, 0) none repeat scroll 0% 0%; width: 7px; position: absolute; top: 0px; opacity: 0.4; display: none; border-radius: 7px; z-index: 99; right: 1px; height: 728px;"></div>
                                <div class="slimScrollRail" style="width: 7px; height: 100%; position: absolute; top: 0px; display: none; border-radius: 7px; background: rgb(51, 51, 51) none repeat scroll 0% 0%; opacity: 0.2; z-index: 90; right: 1px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection