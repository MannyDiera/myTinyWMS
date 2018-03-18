<?php

namespace Mss\Http\Controllers;


use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Mss\Http\Requests\NewOrderMessageRequest;
use Mss\Mail\SupplierMail;
use Mss\Models\Order;
use Mss\Models\OrderMessage;
use Webpatser\Uuid\Uuid;

class OrderMessageController extends Controller {

    public function create(Order $order) {
        $order->load(['items.article' => function ($query) {
            $query->withCurrentSupplierArticle();
        }]);

        $preSetBody = null;
        $preSetReceiver = null;
        $preSetSubject = null;

        if (request('answer')) {
            $orgMessage = OrderMessage::find(request('answer'));
            $preSetReceiver = $orgMessage->sender->contains('System') ? '' : $orgMessage->sender->implode(',');
            $preSetBody = '<br/><br/>Am '.$orgMessage->received->formatLocalized('%A, %d.%B %Y, %H:%M Uhr').' schrieb '.$orgMessage->sender->contains('System') ? env('MAIL_FROM_ADDRESS') : $orgMessage->sender->first().':<br/><blockquote style="padding: 10px 20px;margin: 5px 0 20px;border-left: 5px solid #eee;">'.$orgMessage->htmlBody.'</blockquote>';
        }

        if (request('sendorder')) {
            $preSetBody = view('emails.new_order', compact('order'))->render();
            $preSetSubject = 'Unsere Bestellung '.$order->internal_order_number;
        }

        return view('order.message_create', compact('order', 'preSetBody', 'preSetReceiver', 'preSetSubject'));
    }

    /**
     * @param Order $order
     * @param NewOrderMessageRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Order $order, NewOrderMessageRequest $request) {
        $attachments = collect(json_decode($request->get('attachments'), true));
        $attachments->transform(function ($attachment) {
            $fileName = Uuid::generate(4)->string;
            if (Storage::move($attachment['tempFile'], 'attachments/'.$fileName)) {
                return [
                    'fileName' => $fileName,
                    'contentType' => $attachment['type'],
                    'orgFileName' => $attachment['orgName']
                ];
            }
        });

        $receivers = collect(explode(',', $request->get('receiver')))->transform(function ($receiver) {
            return trim($receiver);
        });

        if (count($receivers) > 1) {
            Mail::to($receivers->first())->cc($receivers->slice(1))->send(new SupplierMail (
                $request->get('subject'), $request->get('body'), $attachments
            ));
        } else {
            Mail::to($receivers)->send(new SupplierMail (
                $request->get('subject'), $request->get('body'), $attachments
            ));
        }

        $order->messages()->create([
            'user_id' => Auth::id(),
            'sender' => ['System'],
            'receiver' => $receivers,
            'subject' => $request->get('subject'),
            'htmlBody' => $request->get('body'),
            'attachments' => $attachments,
            'read' => true,
            'received' => Carbon::now()
        ]);

        flash('Nachricht verschickt')->success();

        return response()->redirectToRoute('order.show', $order);
    }

    public function delete(Order $order, OrderMessage $message) {
        $message->delete();

        flash('Nachricht gelöscht')->success();

        return response()->redirectToRoute('order.show', $order);
    }

    public function markUnread(Order $order, OrderMessage $message) {
        $message->read = false;
        $message->save();

        return response()->redirectToRoute('order.show', $order);
    }

    public function markRead(Order $order, OrderMessage $message) {
        $message->read = true;
        $message->save();

        return response()->redirectToRoute('order.show', $order);
    }

    public function uploadNewAttachments(Order $order, Request $request) {
        $file = $request->file('file');

        $upload_success = $file->storeAs('upload_temp', $order->id.'_'.Uuid::generate(4)->string);
        if ($upload_success) {
            return response()->json($upload_success, 200);
        } else {
            return response()->json('error', 400);
        }
    }

    public function unassignedMessages() {
        $unassignedMessages = OrderMessage::unassigned()->get();

        return view('order.unsassigned_messages', compact('unassignedMessages'));
    }

    public function messageAttachmentDownload(OrderMessage $message, $attachment) {
        $attachment = $message->attachments->where('fileName', $attachment)->first();
        return response()->download(storage_path('attachments/'.$attachment['fileName']), $attachment['orgFileName'], ['Content-Type' => $attachment['contentType']]);
    }
}