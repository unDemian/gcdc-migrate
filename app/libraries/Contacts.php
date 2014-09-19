<?

namespace app\libraries;

use app\models\Backups;
use app\models\BackupsModel;
use app\models\MigratedDataModel;
use app\models\ServicesModel;
use app\models\SharesModel;
use app\models\TasksServicesModel;


class Contacts extends \Entity
{
	public static $name  = 'Contacts';
	public static $limit = 50;

	public static $kind  = array(
		'contact'   => 'contacts#contact',
	);

	public static $endpoints = array(
		'contacts'  => 'https://www.google.com/m8/feeds/contacts/default/full',
	);

	public static function backup($user, $taskId = 0, $syncTaskId = 0, $ignoreUpdate = false)
	{
		// Stats
		$stats = array(
			'contacts'  => 0,
		);

		$contactsFeed = \Rest::get(
			static::$endpoints['contacts'],
			array(),
			$user
		);

		if(isset($contactsFeed->entry)) {
			foreach($contactsFeed->entry as $entry) {

				$contact = array();

				$contact['id']     = @basename($entry->id);
				$contact['name']  = (string) $entry->title;

				$data = $entry->children('http://schemas.google.com/g/2005');

				// Email Address
				if($data->email) {
					foreach($data->email as $entity) {
						$attributes = (array) $entity->attributes();
						$contact['emails'][] = $attributes['@attributes'];
					}
				}
				// Instant messaging
				if($data->im) {
					foreach($data->im as $entity) {
						$attributes = (array) $entity->attributes();
						$contact['im'][] = $attributes['@attributes'];
					}
				}

				// Phone numbers
				if($data->phoneNumber) {
					foreach($data->phoneNumber as $entity) {
						$attributes = (array) $entity->attributes();
						$attributes = $attributes['@attributes'];
						$attributes['number'] = (string) $entity;
						$contact['phoneNumbers'][] = $attributes;
					}
				}

				// Postal addresses
				if($data->postalAddress) {
					foreach($data->postalAddress as $entity) {
						$attributes = (array) $entity->attributes();
						$attributes = $attributes['@attributes'];
						$attributes['address'] = (string) $entity;
						$contact['postalAddress'][] = $attributes;
					}
				}

				// Save Contact
				if($contact) {
					$backup = BackupsModel::create();
					$backup->user_id        = $user['username']['id'];
					$backup->task_id        = $taskId;
					$backup->sync_task_id   = $syncTaskId;
					$backup->entity_id      = $contact['id'];
					$backup->entity_type    = static::$kind['contact'];
					$backup->entity_title   = $contact['name'];
					$backup->entity_picture = \Render::image('no-photo.jpg');
					$backup->entity         = json_encode($contact);
					$backup->created        = date(DATE_TIME);
					$backup->save();
				}

				$stats['contacts']++;
			}
		}

		return $stats;
	}

	public static function share($user)
	{
		// Stats
		$stats = array(
			'contacts'  => array(),
		);

		$contactsFeed = \Rest::get(
			static::$endpoints['contacts'],
			array(),
			$user
		);

		if(isset($contactsFeed->entry)) {
			foreach($contactsFeed->entry as $entry) {
				$contact = array();

				$contact['id']     = @basename($entry->id);
				$contact['name']  = (string) $entry->title;

				$data = $entry->children('http://schemas.google.com/g/2005');

				// Email Address
				if($data->email) {
					foreach($data->email as $entity) {
						$attributes = (array) $entity->attributes();
						$newContact['emails'][] = $attributes['@attributes'];
					}
				}
				// Instant messaging
				if($data->im) {
					foreach($data->im as $entity) {
						$attributes = (array) $entity->attributes();
						$contact['im'][] = $attributes['@attributes'];
					}
				}

				// Phone numbers
				if($data->phoneNumber) {
					foreach($data->phoneNumber as $entity) {
						$attributes = (array) $entity->attributes();
						$attributes = $attributes['@attributes'];
						$attributes['number'] = (string) $entity;
						$contact['phoneNumbers'][] = $attributes;
					}
				}

				// Postal addresses
				if($data->postalAddress) {
					foreach($data->postalAddress as $entity) {
						$attributes = (array) $entity->attributes();
						$attributes = $attributes['@attributes'];
						$attributes['address'] = (string) $entity;
						$contact['postalAddress'][] = $attributes;
					}
				}

				$stats['contacts'][] = array(
					'id'      => $contact['id'],
					'name'    => $contact['name'],
					'picture' => \Render::image('no-photo.jpg'),
					'data'    => $contact
				);
			}
		}

		return $stats;
	}

	public static function shared($task)
	{
		$stats = array(
			'contacts'      => array()
		);

		// Get contacts
		$contacts = BackupsModel::all(array('sync_task_id' => $task['id'], 'user_id' => $task['user_id'], 'entity_type' => static::$kind['contact']))->toArray();
		if($contacts) {
			foreach($contacts as $contact) {
				$data = json_decode($contact['entity'], true);

				$stats['contacts'][] = array(
					'id'      => $contact['entity_id'],
					'name'    => $contact['entity_title'],
					'picture' => \Render::image('no-photo.jpg'),
					'data'    => $data
				);
			}
		}

		return $stats;
	}

	public static function clean($source, $destination, $syncTaskId = 0, $ignoreUpdate = false)
	{
		$share = SharesModel::first(array('task_id' => $syncTaskId));

		if($share) {
			$share = $share->toArray();
			$data  = json_decode($share['data'], true);

			static::_clean($source, $source, $syncTaskId, $data);
		}
	}

	public static function _transfer($source, $destination, $syncTaskId = 0, $ignoreUpdate = false, $whitelist = array())
	{
		// Stats
		$stats = array(
			'contacts' => 0,
		);

		// Get source data
		$contacts            = BackupsModel::all(array('user_id' => $source['username']['id'], 'sync_task_id' => $syncTaskId, 'entity_type' => static::$kind['contact']))->toArray();
		$destinationContacts = BackupsModel::all(array('user_id' => $destination['username']['id'], 'sync_task_id' => $syncTaskId, 'entity_type' => static::$kind['contact']))->column('entity_title');
		$syncedContacts      = MigratedDataModel::all(array('source_id' => $source['username']['id'], 'destination_id' => $destination['username']['id'], 'status' => MigratedDataModel::STATUS_ACTIVE, 'kind' => static::$kind['contact']))->column('identifier');

		if($contacts) {
			foreach($contacts as $contact) {

				$contactData = json_decode($contact['entity'], true);

				// Whitelisting used for share feature
				$whiteListed = true;
				if($whitelist) {
					if(isset($whitelist['contacts']) && $whitelist['contacts']) {
						if( !in_array($contactData['id'], $whitelist['contacts'])) {
							$whiteListed = false;
						}
					}
				}


				if( !in_array(md5($contactData['name']), $syncedContacts) && $whiteListed) {

					$body = <<<EOD
<?xml version='1.0' encoding='UTF-8'?>
<entry
	xmlns='http://www.w3.org/2005/Atom'
	xmlns:gd='http://schemas.google.com/g/2005'>

EOD;
					if(in_array($contactData['name'], $destinationContacts) && !$ignoreUpdate) {
						$contactData['name'] = $contactData['name'] . ' (2)';
					} else {
						$contactData['name'] = $contactData['name'];
					}

					$body .= '<title type="text">' . $contactData['name'] . '</title>';

					// Emails
					if(isset($contactData['emails']) && $contactData['emails']) {
						foreach($contactData['emails'] as $email) {
							$body .= '<gd:email rel="' . (isset($email['rel']) ? $email['rel'] : 'http://schemas.google.com/g/2005#other' ) . '"';

							if(isset($email['primary'])) {
								$body .= ' primary="true" ';
							}

							$body .= ' address="' . $email['address'] . '" />';
						}
					}

					// IM
					if(isset($contactData['im']) && $contactData['im']) {
						foreach($contactData['im'] as $im) {
							$body .= '<gd:im address="' . $im['address'] . '" protocol="' . $im['protocol'] . '" rel="' . $im['rel'] . '"/>';
						}
					}

					// Numbers
					if(isset($contactData['phoneNumbers']) && $contactData['phoneNumbers']) {
						foreach($contactData['phoneNumbers'] as $number) {
							$body .= '<gd:phoneNumber rel="' . $number['rel'] . '">' . $number['number'] . '</gd:phoneNumber>';
						}
					}

					// Postal

					if(isset($contactData['postalAddress']) && $contactData['postalAddress']) {
						foreach($contactData['postalAddress'] as $address) {
							$body .= '<gd:postalAddress rel="' . $address['rel'] . '">' . $address['address'] . '</gd:postalAddress>';
						}
					}
					$body .= '</entry>';

					$newContact = \Rest::postXML(static::$endpoints['contacts'], $body, $destination);

					// Update playlist with new id
					if( !$ignoreUpdate && $newContact) {

						$newContactId = @basename($newContact->id);

						if($newContactId) {
							$oldPlaylist = BackupsModel::first($contact['id']);
							$oldPlaylist->entity_new_id = $newContactId;
							$oldPlaylist->save();
						}
					}

					$stats['contacts']++;

					$syncedContact = MigratedDataModel::create();
					$syncedContact->source_id      = $source['username']['id'];
					$syncedContact->destination_id = $destination['username']['id'];
					$syncedContact->task_id        = 0;
					$syncedContact->sync_task_id   = $syncTaskId;
					$syncedContact->table          = BackupsModel::$schema['table'];
					$syncedContact->table_id       = $contact['id'];
					$syncedContact->kind           = static::$kind['contact'];
					$syncedContact->identifier     = md5($contactData['name']);
					$syncedContact->status         = MigratedDataModel::STATUS_ACTIVE;
					$syncedContact->created        = date(DATE_TIME);
					$syncedContact->save();
				}
			}
		}

		return $stats;
	}

	public static function _clean($destination, $source, $syncTaskId = 0, $whitelist = array())
	{
		$service = ServicesModel::first(array('library' => 'Contacts'))->toArray();

		$task = TasksServicesModel::first(array('task_id' => $syncTaskId, 'service_id' => $service['id']))->toArray();
		$task['stats'] = json_decode($task['stats'], true);

		// Contacts
		$contacts = BackupsModel::all(array('user_id' => $source['username']['id'], 'sync_task_id' => $syncTaskId, 'entity_type' => static::$kind['contact']))->toArray();
		if($contacts && ($task['stats']['contacts'] || $destination['username']['id'] == $source['username']['id'])) {
			foreach($contacts as $contact) {

				// $whitelist - shared or clean data
				if($whitelist) {
					if( isset($whitelist['contacts']) && in_array($contact['entity_id'], $whitelist['contacts']) ) {
						\Rest::deleteXML('https://www.google.com/m8/feeds/contacts/default/full/' . $contact['entity_id'], array(), $destination);
					}
				} else {
					if($destination['username']['id'] == $source['username']['id']) {
						\Rest::deleteXML('https://www.google.com/m8/feeds/contacts/default/full/' . $contact['entity_id'], array(), $destination);
					} else {
						if( $contact['entity_new_id']) {
							\Rest::deleteXML('https://www.google.com/m8/feeds/contacts/default/full/' . $contact['entity_new_id'], array(), $destination);
						}
					}
				}
			}
		}
	}

	public static function _cleanDB($syncTaskId)
	{
		// Clear DB data
		MigratedDataModel::softDelete(array('sync_task_id' => $syncTaskId));
	}
}