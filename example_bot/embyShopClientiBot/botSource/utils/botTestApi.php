<?php
class BotTest {
  public $bot          = null;
  private $json         = null;
  private $messageType  = null;
  public $update        = null;

  public function __construct($token, $json = false){
    if($json == false) $this->json = file_get_contents('php://input');
    $this->bot = $token;

    #Variabili
    $this->update = json_decode($this->json, TRUE);
    if($this->update != null){

      if(isset($this->update['message'])) $this->messageType  = 'message';
      else if(isset($this->update['edited_message'])) $this->messageType  = 'edited_message';

      $this->update_id        = $this->update['update_id']                                  ?? null; //ID dell'update

      if(isset($this->update['callback_query']['id'])){ //Se l'update è un callback_query
        $this->update_id                  = $this->update['update_id']                                        ?? null; //ID dell'update
        $this->callback_query_id          = $this->update['callback_query']['id']                             ?? null; //callback_query_id
        $this->callback_user_id           = $this->update['callback_query']['from']['id']                     ?? null; //ID dell'utente che ha cliccato il pulsante
        $this->callback_is_bot            = $this->update['callback_query']['from']['is_bot']                 ?? null; //is_bot dell'utente che ha cliccato il pulsante
        $this->callback_nome              = $this->update['callback_query']['from']['first_name']             ?? null; //nome dell'utente che ha cliccato il pulsante
        $this->callback_cognome           = $this->update['callback_query']['from']['last_name']              ?? null; //nome dell'utente che ha cliccato il pulsante
        $this->callback_username          = $this->update['callback_query']['from']['username']               ?? null; //username dell'utente che ha cliccato il pulsante
        $this->callback_lingua            = $this->update['callback_query']['from']['language_code']          ?? null; //lingua dell'utente che ha cliccato il pulsante
        $this->callback_message_id        = $this->update['callback_query']['message']['message_id']          ?? null; //Message_id del messaggio originale
        $this->callback_bot_id            = $this->update['callback_query']['message']['from']['id']          ?? null; //ID del bot del callback_query
        $this->callback_bot_is_bot        = $this->update['callback_query']['message']['from']['is_bot']      ?? null; //is_bot del bot del callback_query
        $this->callback_bot_nome          = $this->update['callback_query']['message']['from']['first_name']  ?? null; //nome del bot del callback_query
        $this->callback_bot_username      = $this->update['callback_query']['message']['from']['username']    ?? null; //Username del bot del callback_query
        $this->callback_chat_id           = $this->update['callback_query']['message']['chat']['id']          ?? null; //ID del gruppo/chat del callback_query
        $this->callback_chat_title        = $this->update['callback_query']['message']['chat']['title']       ?? null; //Titolo del gruppo/chat del callback_query
        $this->callback_chat_type         = $this->update['callback_query']['message']['chat']['type']        ?? null; //Tipo della chat (gruppo, supergruppo, canale, private)
        $this->callback_time              = $this->update['callback_query']['message']['date']                ?? null; //Timestamp del querydata
        $this->callback_text              = $this->update['callback_query']['message']['text']                ?? null; //Testo del messaggio a cui l'utente ha cliccato
        $this->callback_entities          = $this->update['callback_query']['message']['entities']            ?? null; //Array degli entities
        $this->callback_inline_keyboard   = $this->update['callback_query']['message']['reply_markup']['inline_keyboard'] ?? null; //Array della tastiera del messaggio a cui l'utente ha cliccato
        $this->callback_chat_instance     = $this->update['callback_query']['chat_instance']                              ?? null; //istanza chat
        $this->callback_data              = $this->update['callback_query']['data']                                       ?? null; //callback_data
      }else if(isset($this->update[$this->messageType]['message_id'])){ //Se l'update è un messaggio
        $this->message_id           = $this->update[$this->messageType]['message_id']               ?? null; //Message_id del messaggio
        $this->message_thread_id    = $this->update[$this->messageType]['message_thread_id']        ?? null; //Message_thread_id del messaggio
        if(isset($this->update[$this->messageType]['from'])){
            $this->from_id                          = $this->update[$this->messageType]['from']['id']                           ?? null; //ID dell'utente
            $this->from_is_bot                      = $this->update[$this->messageType]['from']['is_bot']                       ?? null; //is_bot dell'utente (true/false)
            $this->from_first_name                  = $this->update[$this->messageType]['from']['first_name']                   ?? null; //nome dell'utente
            $this->from_last_name                   = $this->update[$this->messageType]['from']['last_name']                    ?? null; //cognome dell'utente
            $this->from_username                    = $this->update[$this->messageType]['from']['username']                     ?? null; //username dell'utente
            $this->from_language_code               = $this->update[$this->messageType]['from']['language_code']                ?? null; //Lingua dell'utente
            $this->from_is_premium                  = $this->update[$this->messageType]['from']['is_premium']                   ?? null; //is_premium dell'utente (true/false)
            $this->from_added_to_attachment_menu    = $this->update[$this->messageType]['from']['added_to_attachment_menu']     ?? null; //added_to_attachment_menu dell'utente (true/false)
            $this->from_can_join_groups             = $this->update[$this->messageType]['from']['can_join_groups']              ?? null; //can_join_groups dell'utente (true/false)
            $this->from_can_read_all_group_messages = $this->update[$this->messageType]['from']['can_read_all_group_messages']  ?? null; //can_read_all_group_messages dell'utente (true/false)
            $this->from_supports_inline_queries     = $this->update[$this->messageType]['from']['supports_inline_queries']      ?? null; //supports_inline_queries dell'utente (true/false)
            $this->from_can_connect_to_business     = $this->update[$this->messageType]['from']['can_connect_to_business']      ?? null; //can_connect_to_business dell'utente (true/false)
            $this->from_has_main_web_app            = $this->update[$this->messageType]['from']['has_main_web_app']             ?? null; //has_main_web_app dell'utente (true/false)
        }

        $this->date                     = $this->update[$this->messageType]['date']                     ?? null; //tempo della chat (gruppo, canale, utente)
        $this->business_connection_id   = $this->update[$this->messageType]['business_connection_id']   ?? null; //business_connection_id del messaggio

        if(isset($this->update[$this->messageType]['chat'])){
            $this->chat_id              = $this->update[$this->messageType]['chat']['id']               ?? null; //ID della chat (gruppo, canale, utente)
            $this->chat_type            = $this->update[$this->messageType]['chat']['type']             ?? null; //tipo della chat (gruppo, canale, utente)
            $this->chat_title           = $this->update[$this->messageType]['chat']['title']            ?? null; //titolo chat
            $this->chat_username        = $this->update[$this->messageType]['chat']['username']         ?? null; //username della chat (gruppo, canale, utente)
            $this->chat_first_name      = $this->update[$this->messageType]['chat']['last_name']        ?? null; //cognome dell'utente
            $this->chat_last_name       = $this->update[$this->messageType]['chat']['username']         ?? null; //username dell'utente
            $this->chat_is_forum        = $this->update[$this->messageType]['chat']['is_forum']         ?? null; //is_forum della chat (true/false)
        }

        if(isset($this->update[$this->messageType]['forward_origin'])){
            $this->forward_origin_type = $this->update[$this->messageType]['forward_origin']['type']                            ?? null;
            $this->forward_origin_date = $this->update[$this->messageType]['forward_origin']['date']                            ?? null;
            $this->forward_origin_sender_user = $this->update[$this->messageType]['forward_origin']['user']                     ?? null;
            $this->forward_origin_sender_name = $this->update[$this->messageType]['forward_origin']['sender_name']              ?? null;
            $this->forward_origin_author_signature = $this->update[$this->messageType]['forward_origin']['author_signature']    ?? null;
            $this->forward_origin_message_id = $this->update[$this->messageType]['forward_origin']['message_id']                ?? null;


            if(isset($this->update[$this->messageType]['forward_origin']['chat'])){
                $this->forward_origin_chat_id              = $this->update[$this->messageType]['forward_origin']['chat']['id']               ?? null; 
                $this->forward_origin_chat_type            = $this->update[$this->messageType]['forward_origin']['chat']['type']             ?? null; 
                $this->forward_origin_chat_title           = $this->update[$this->messageType]['forward_origin']['chat']['title']            ?? null; 
                $this->forward_origin_chat_username        = $this->update[$this->messageType]['forward_origin']['chat']['username']         ?? null; 
                $this->forward_origin_chat_first_name      = $this->update[$this->messageType]['forward_origin']['chat']['last_name']        ?? null; 
                $this->forward_origin_chat_last_name       = $this->update[$this->messageType]['forward_origin']['chat']['username']         ?? null; 
                $this->forward_origin_chat_is_forum        = $this->update[$this->messageType]['forward_origin']['chat']['is_forum']         ?? null; 
            }   
        }

        $this->is_topic_message         = $this->update[$this->messageType]['is_topic_message']         ?? null; //is_topic_message del messaggio (true/false)
        $this->is_automatic_forward     = $this->update[$this->messageType]['is_automatic_forward']     ?? null; //is_automatic_forward del messaggio (true/false)


        $this->edit_date                = $this->update[$this->messageType]['edit_date']                ?? null; //edit_date del messaggio
        $this->has_protected_content    = $this->update[$this->messageType]['has_protected_content']    ?? null; //has_protected_content del messaggio (true/false)
        $this->is_from_offline          = $this->update[$this->messageType]['is_from_offline']          ?? null; //is_from_offline del messaggio (true/false)
        $this->media_group_id           = $this->update[$this->messageType]['media_group_id']           ?? null; //media_group_id del messaggio
        $this->author_signature         = $this->update[$this->messageType]['author_signature']         ?? null; //author_signature del messaggio
        $this->text                     = $this->update[$this->messageType]['text']                     ?? null; //testo del messaggio
        $this->entities                 = $this->update[$this->messageType]['entities']                 ?? null; //Array delle entities
        $this->effect_id                = $this->update[$this->messageType]['effect_id']                ?? null; //effect_id del messaggio
        $this->caption                  = $this->update[$this->messageType]['caption']                  ?? null; //didascalia del messaggio
        $this->caption_entities         = $this->update[$this->messageType]['caption_entities']         ?? null; //Array delle entities della didascalia
        $this->show_caption_above_media = $this->update[$this->messageType]['show_caption_above_media'] ?? null; //show_caption_above_media del messaggio (true/false)
        $this->has_media_spoiler        = $this->update[$this->messageType]['has_media_spoiler']        ?? null; //has_media_spoiler del messaggio (true/false)
        $this->new_chat_title           = $this->update[$this->messageType]['new_chat_title']           ?? null; //new_chat_title del messaggio
        $this->delete_chat_photo        = $this->update[$this->messageType]['delete_chat_photo']        ?? null; //delete_chat_photo del messaggio (true/false)
        $this->group_chat_created       = $this->update[$this->messageType]['group_chat_created']       ?? null; //group_chat_created del messaggio (true/false)
        $this->supergroup_chat_created  = $this->update[$this->messageType]['supergroup_chat_created']  ?? null; //supergroup_chat_created del messaggio (true/false)
        $this->channel_chat_created     = $this->update[$this->messageType]['channel_chat_created']     ?? null; //channel_chat_created del messaggio (true/false)
        $this->migrate_to_chat_id       = $this->update[$this->messageType]['migrate_to_chat_id']       ?? null; //migrate_to_chat_id del messaggio
        $this->migrate_from_chat_id     = $this->update[$this->messageType]['migrate_from_chat_id']     ?? null; //migrate_from_chat_id del messaggio
        $this->connected_website        = $this->update[$this->messageType]['connected_website']     ?? null; //connected_website del messaggio

        // STICKER
        if(isset($this->update[$this->messageType]['sticker'])){
            $this->sticker_id           = $this->update[$this->messageType]['sticker']['file_id']           ?? null; //File_id dello sticker
            $this->stcker_unique_id     = $this->update[$this->messageType]['sticker']['file_unique_id']    ?? null; //File_unique_id dello sticker
            $this->stcker_type          = $this->update[$this->messageType]['sticker']['type']              ?? null; //Tipo dello sticker
            $this->sticker_width        = $this->update[$this->messageType]['sticker']['width']             ?? null; //Larghezza sticker
            $this->sticker_height       = $this->update[$this->messageType]['sticker']['height']            ?? null; //Altezza sticker
            $this->sticker_is_animated  = $this->update[$this->messageType]['sticker']['is_animated']       ?? null; //Se è animato [true/false]
            $this->sticker_is_video     = $this->update[$this->messageType]['sticker']['is_video']          ?? null; //Se è un video [true/false]
            $this->sticker_emoji        = $this->update[$this->messageType]['sticker']['emoji']             ?? null; //Emoji sticker
            $this->sticker_name         = $this->update[$this->messageType]['sticker']['set_name']          ?? null; //Nome dello sticker
            $this->sticker_size         = $this->update[$this->messageType]['sticker']['file_size']         ?? null; //Peso dello sticker
        }

        // FOTO
        if(isset($this->update[$this->messageType]['photo'])){
            $this->file_unique_id = $this->update[$this->messageType]['photo']['file_unique_id']    ?? null; //file_id del file
            $this->foto           = $this->update[$this->messageType]['photo']['2']['file_id']      ?? null; //File_id della foto inviata
            $this->foto_array     = $this->update[$this->messageType]['photo']                      ?? null; //Array delle foto inviate, necessario selezionare indice e inserire ['file_id']
        }

        // DOCUMENTI
        if(isset($this->update[$this->messageType]['document'])){ 
            $this->file_id              = $this->update[$this->messageType]['document']['file_id']          ?? null; //file_id del file
            $this->file_unique_id       = $this->update[$this->messageType]['document']['file_unique_id']   ?? null; //file_id del file
            $this->file_name            = $this->update[$this->messageType]['document']['file_name']        ?? null; //Nome del file
            $this->file_mine_size       = $this->update[$this->messageType]['document']['mime_type']        ?? null; //Estensione del file
            $this->file_size            = $this->update[$this->messageType]['document']['file_size']        ?? null; //Peso del file
        }

        // VIDEO
        if(isset($this->update[$this->messageType]['video'])){ //Se è un video
          $this->durata_video     = $this->update[$this->messageType]['video']['duration']        ?? null; //Durata video
          $this->video            = $this->update[$this->messageType]['video']['file_id']         ?? null; //File_id del video
          $this->tipo_video       = $this->update[$this->messageType]['video']['mime_type']       ?? null; //Estensione del video
          $this->file_unique_id   = $this->update[$this->messageType]['video']['file_unique_id']  ?? null; //file_id del file
          $this->width_video      = $this->update[$this->messageType]['video']['width']           ?? null;
          $this->size_video       = $this->update[$this->messageType]['video']['file_size']       ?? null;
          $this->height_video     = $this->update[$this->messageType]['video']['height']          ?? null;
        }

        // GIF
        if(isset($this->update[$this->messageType]['animation'])){
          $this->durata_gif       = $this->update[$this->messageType]['animation']['duration']    ?? null; //Durata video
          $this->gif              = $this->update[$this->messageType]['animation']['file_id']     ?? null; //File_id del video
          $this->tipo_gif         = $this->update[$this->messageType]['animation']['mime_type']   ?? null; //Estensione del video
          $this->width_gif        = $this->update[$this->messageType]['animation']['width']       ?? null;
          $this->size_gif         = $this->update[$this->messageType]['animation']['file_size']   ?? null;
          $this->height_gif       = $this->update[$this->messageType]['animation']['height']      ?? null;
        }

        // AUDIO
        if(isset($this->update[$this->messageType]['audio'])){
          $this->durata_audio = $this->update[$this->messageType]['audio']['duration']            ?? null; //Durata audio
          $this->audio_id     = $this->update[$this->messageType]['audio']['file_id']             ?? null; //File_id del audio
          $this->tipo_audio   = $this->update[$this->messageType]['audio']['mime_type']           ?? null; //Estensione del audio
          $this->width_audio  = $this->update[$this->messageType]['audio']['width']               ?? null;
          $this->size_audio   = $this->update[$this->messageType]['audio']['file_size']           ?? null;
          $this->height_audio = $this->update[$this->messageType]['audio']['height']              ?? null;  
        } 

        // VOICE
        if(isset($this->update[$this->messageType]['voice'])){
          $this->durata_voice = $this->update[$this->messageType]['voice']['duration']            ?? null; //Durata voice
          $this->voice_id     = $this->update[$this->messageType]['voice']['file_id']             ?? null; //File_id del voice
          $this->tipo_voice   = $this->update[$this->messageType]['voice']['mime_type']           ?? null; //Estensione del voice
          $this->width_voice  = $this->update[$this->messageType]['voice']['width']               ?? null;
          $this->size_voice   = $this->update[$this->messageType]['voice']['file_size']           ?? null;
          $this->height_voice = $this->update[$this->messageType]['voice']['height']              ?? null;
        }     

        if(isset($this->update[$this->messageType]['forward_sender_name'])){ //Se il messaggio è INOLTRATO, ma l'utente ha la privacy mode ON
          $this->forward_sender_name  = $this->update[$this->messageType]['forward_sender_name']          ?? null; //Nome del tizio forwardato
          $this->forward_date         = $this->update[$this->messageType]['forward_date']                 ?? null; //Timestamp messaggio forwardato
          $this->forward_text         = $this->update[$this->messageType]['text']                         ?? null; //Testo del messaggio forwardato
        } else if(isset($this->update[$this->messageType]['forward_from'])){ //Se il messaggio è INOLTRATO
          $this->forward_chat_id      = $this->update[$this->messageType]['forward_from']['id']           ?? null;//ID del messaggio forwardato (non message_id)
          $this->forward_is_bot       = $this->update[$this->messageType]['forward_from']['is_bot']       ?? null; //is_bot del messaggio forwardato
          $this->forward_nome         = $this->update[$this->messageType]['forward_from']['first_name']   ?? null; //nome del messaggio forwardato
          $this->forward_cognome      = $this->update[$this->messageType]['forward_from']['last_name']    ?? null; //cognome del messaggio forwardato
          $this->forward_username     = $this->update[$this->messageType]['forward_from']['username']     ?? null; //username del messaggio forwardato
          $this->forward_text         = $this->update[$this->messageType]['text']                         ?? null; //Testo del messaggio forwardato
          $this->forward_date         = $this->update[$this->messageType]['forward_date']                 ?? null; //Timestamp messaggio forwardato
        } else if(isset($this->update[$this->messageType]['forward_from_chat'])){ 
          $this->forward_chat_id          = $this->update[$this->messageType]['forward_from_chat']['id']        ?? null;
          $this->forward_title            = $this->update[$this->messageType]['forward_from_chat']['title']     ?? null;
          $this->forward_username         = $this->update[$this->messageType]['forward_from_chat']['username']  ?? null;
          $this->forward_type             = $this->update[$this->messageType]['forward_from_chat']['type']      ?? null;
          $this->forward_from_message_id  = $this->update[$this->messageType]['forward_from_message_id']        ?? null;
          $this->forward_date             = $this->update[$this->messageType]['forward_date']                   ?? null;
        }

        // REPLY
        if(isset($this->update[$this->messageType]['reply_to_message']['message_id'])){
          $this->reply_message_id   = $this->update[$this->messageType]['reply_to_message']['message_id']           ?? null; //ID del messaggio a cui si stà rispondendo
          $this->reply_user_id      = $this->update[$this->messageType]['reply_to_message']['from']['id']           ?? null; //id dell'utente a cui si ha risposto
          $this->reply_is_bot       = $this->update[$this->messageType]['reply_to_message']['from']['is_bot']       ?? null; //is_bot dell'utente o bot a cui si ha risposto
          $this->reply_nome         = $this->update[$this->messageType]['reply_to_message']['from']['first_name']   ?? null; //Nome dell'utente a cui si ha risposto
          $this->reply_cognome      = $this->update[$this->messageType]['reply_to_message']['from']['last_name']    ?? null; //Nome dell'utente a cui si ha risposto
          $this->reply_tipo         = $this->update[$this->messageType]['reply_to_message']['from']['type']         ?? null; //tipo del messaggio nella chat a cui si stà rispondendo
          $this->reply_time         = $this->update[$this->messageType]['reply_to_message']['from']['date']         ?? null; //data messaggio a cui si stà rispondendo
          $this->reply_username     = $this->update[$this->messageType]['reply_to_message']['from']['username']     ?? null; //username della chat (utente, gruppo, canale) del messaggio a cui si sta rispondendo
          $this->reply_chat_id      = $this->update[$this->messageType]['reply_to_message']['chat']['id']           ?? null; //ID della chat (utente, gruppo, canale) del messaggio a cui si sta rispondendo
          $this->reply_chat_nome    = $this->update[$this->messageType]['reply_to_message']['chat']['first_name']   ?? null; //nome della chat (utente, gruppo, canale) del messaggio a cui si sta rispondendo
          $this->reply_chat_cognome = $this->update[$this->messageType]['reply_to_message']['chat']['last_name']    ?? null; //cognome della chat (utente, gruppo, canale) del messaggio a cui si sta rispondendo
          $this->reply_chat_tipo    = $this->update[$this->messageType]['reply_to_message']['chat']['type']         ?? null; //tipo della chat (utente, gruppo, canale) del messaggio a cui si sta rispondendo
          $this->reply_time         = $this->update[$this->messageType]['reply_to_message']['date']                 ?? null; //data della chat a cui si stà rispondendo
          $this->reply_text         = $this->update[$this->messageType]['reply_to_message']['text']                 ?? null; //testo del messaggio a cui si stà rispondendo
          $this->reply_entities     = $this->update[$this->messageType]['reply_to_message']['entities']             ?? null; //array delle entities del messaggio a cui si stà rispondendo
          $this->reply_to_message_photo_0_file_id = $this->update[$this->messageType]['reply_to_message']['photo'][0]['file_id'] ?? null; //file_id della foto a cui si sta rispondendo
        }//Fine reply_to_message
      } //Fine verifica messaggio
    } //Fine verifica json
  } //Fine __construct

  #FUNZIONI
  public function cURL($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 6);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
    curl_setopt($ch, CURLOPT_URL, $url);
    $output = curl_exec($ch);
    curl_close($ch);
    return json_decode($output, TRUE);
  }

  public function printJson($chat_id, $json){
    $this->sendMessage($chat_id, json_encode($json, JSON_PRETTY_PRINT));
  }

  public function sendMessage($chat_id, $text, $keyboard = '', $reply_message_id = false){
    if ($keyboard != '') $keyboard = '&reply_markup={"inline_keyboard":['.urlencode($keyboard).'],"resize_keyboard":true}';
 
    if($reply_message_id != false) $reply_message_id = '&reply_to_message_id='.$reply_message_id;
    else $reply_message_id = '';
 
    $url = 'https://api.telegram.org/bot'.$this->bot."/test/sendMessage?chat_id=$chat_id&text=" . urlencode($text) . '&parse_mode=HTML&disable_web_page_preview=true' . $keyboard . $reply_message_id . '&force_reply=false&disable_notification=false';
    return $this->cURL($url);
  }

  public function deleteMessage($chat_id, $message_id){
    $url = 'https://api.telegram.org/bot'.$this->bot."/test/deleteMessage?chat_id=$chat_id&message_id=$message_id";
    return $this->cURL($url);
  }

  public function editMessageText($chat_id, $message_id, $text, $keyboard = ''){
    if ($keyboard != '') $keyboard = '&reply_markup={"inline_keyboard":['.urlencode($keyboard).'],"resize_keyboard":true}';

    $url = 'https://api.telegram.org/bot'.$this->bot."/test/editMessageText?chat_id=$chat_id&message_id=$message_id&text=".urlencode($text) . '&disable_web_page_preview=true&parse_mode=HTML'. $keyboard;
    return $this->cURL($url);
  }

  public function forwardMessage($from_chat_id, $chat_id, $message_id){
    $url = 'https://api.telegram.org/bot'.$this->bot."/test/forwardMessage?from_chat_id=$from_chat_id&chat_id=$chat_id&message_id=$message_id";
    return $this->cURL($url);
  }
 
  public function sendChatAction($chat_id, $action = 'typing'){
    $url = 'https://api.telegram.org/bot'.$this->bot."/test/sendChatAction?chat_id=$chat_id&action=$action";
    return $this->cURL($url);
  }
 
  public function sendSticker($chat_id, $sticker){
    $url = 'https://api.telegram.org/bot'.$this->bot."/test/sendSticker?chat_id=$chat_id&sticker=$sticker";
    return $this->cURL($url);
  }

  public function sendPhoto($chat_id, $photo, $caption = '', $keyboard = '', $file_id = true){
    if ($keyboard != '') $keyboard = '{"inline_keyboard":['.$keyboard.'],"resize_keyboard":true}';

    $ch = curl_init();

    if($file_id == true){
      $args = [
        'caption' => $caption,
        'chat_id' => $chat_id,
        'photo' => $photo,
        'reply_markup' => $keyboard,
        'parse_mode' => 'HTML'
      ];
    } else {
       curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:multipart/form-data']);
       $photoFile = new CURLFile($photo);
       $args = [
         'caption' => $caption,
         'chat_id' => $chat_id,
         'photo' => $photoFile,
         'reply_markup' => $keyboard,
         'parse_mode' => 'HTML'
      ];
    }

    curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot'.$this->bot.'/test/sendPhoto');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
    $output = curl_exec($ch);
    curl_close($ch);
    return json_decode($output, TRUE);
  }
 
  public function sendDocument($chat_id, $document, $caption = '', $keyboard = '', $file_id = true){
    if ($keyboard != '') $keyboard = '{"inline_keyboard":['.$keyboard.'],"resize_keyboard":true}';
 
    $ch = curl_init();
 
    if($file_id == true){
      $args = [
        'caption' => $caption,
        'chat_id' => $chat_id,
        'document' => $document,
        'reply_markup' => $keyboard,
        'parse_mode' => 'HTML'
      ];
    } else {
      curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:multipart/form-data']);
      $documentID = new CURLFile($document);
      $args = [
        'caption' => $caption,
        'chat_id' => $chat_id,
        'document' => $documentID,
        'reply_markup' => $keyboard,
        'parse_mode' => 'HTML'
      ];
    }
 
    curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot'.$this->bot.'/test/sendDocument');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
    $output = curl_exec($ch);
    curl_close($ch);
    return json_decode($output, TRUE);
  }
 
  public function answerCallbackQuery($callback_query_id, $text){
    $url = 'https://api.telegram.org/bot'.$this->bot."/test/answerCallbackQuery?callback_query_id=$callback_query_id&text=".urlencode($text).'&show_alert=true';
    return $this->cURL($url);
  }
 
  public function getChat($chat_id){
    $url = 'https://api.telegram.org/bot'.$this->bot."/test/getChat?chat_id=$chat_id";
    return $this->cURL($url);
  }
 
  public function sendInvoice($chat_id, $title, $description, $payload, $prices, $provider_token='', $currency = 'XTR'){
    if($provider_token != '') $provider_token = '&provider_token='.$provider_token;
    $prices = json_encode($prices);

    $url = 'https://api.telegram.org/bot'.$this->bot."/test/sendInvoice?chat_id=$chat_id&title=".urlencode($title)."&description=".urlencode($description)."&payload=$payload".$provider_token."&currency=$currency&prices=[$prices]";
    return $this->cURL($url);
  }
 
  public function answerPreCheckoutQuery($pre_checkout_query_id, $ok){
    $url = 'https://api.telegram.org/bot'.$this->bot."/test/answerPreCheckoutQuery?pre_checkout_query_id=$pre_checkout_query_id&ok=$ok";
    return $this->cURL($url);
  }
} //Fine della classe

class BotUtils {
  function setWebhook($token, $url, $max_connections = 40, $secret = 'EmbyShopBot2025'){
    $url = 'https://api.telegram.org/bot'.$token.'/test/setWebhook?max_connections='.$max_connections.'&secret_token='.$secret.'&drop_pending_updates=true&url='.$url;
    return $this->cURL($url);
  }
 
  public function deleteWebhook($token){
    $url = 'https://api.telegram.org/bot'.$token.'/test/deleteWebhook';
    return $this->cURL($url);
  }

  public function getWebhookInfo($token){
    $url = 'https://api.telegram.org/bot'.$token.'/test/getWebhookInfo';
    return $this->cURL($url);
  }
}

//https://api.telegram.org/bot5000230727:AAFtctRVhoikULzxgR95xLLRTzs6uY0Lysg/test/setWebhook?max_connections=40&secret_token=EmbyShopBot2025&drop_pending_updates=true&url=https://telegram.sonicmaster.it/embyShopBot/index.php?token=5000230727:AAFtctRVhoikULzxgR95xLLRTzs6uY0Lysg