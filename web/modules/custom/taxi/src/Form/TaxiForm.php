<?php

namespace Drupal\taxi\Form;

use Drupal\user\Entity\User;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Our Form Class.
 */
class TaxiForm extends FormBase {

  /**
   * Calculates Amount of Cars in Form. Default - 1.
   *
   * @var int
   */
  protected int  $cars = 1;

  /**
   * Gets a Form ID.
   */
  public function getFormId(): string {
    return 'taxi_form';
  }

  /**
   * Recursive Function Builds Tables, and it's Amount.
   *
   * @param array $form
   *   Our Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   It's Form State.
   * @param int $car
   *   It's Table Key(Current Position of Recursive Function).
   */
  public function carsConfigure(array &$form, FormStateInterface $form_state, int $car) {
    // This Time We Move Down->Up.
    // Condition of Recursion Working.
    if ($car < $this->cars) {
      $form['car' . $car] = [
        '#type' => 'details',
        '#title' => $this->t('Configure Your Car'),
        '#open' => TRUE,
        '#attributes' => [
          'class' => ['taxi-car'],
        ],
      ];
      // Set Amount of Adults.
      $form['car' . $car]['adults' . $car] = [
        '#title' => 'Amount of Adults Car №' . $car,
        '#type' => 'number',
        '#required' => TRUE,
        '#min' => 0,
        '#max' => 10,
        '#placeholder' => $this->t("Enter Amount of Adults"),
        '#attributes' => [
          'class' => ['taxi-adults'],
        ],
      ];
      // Set Amount of Children.
      $form['car' . $car]['children' . $car] = [
        '#title' => 'Amount of Children Car №' . $car,
        '#type' => 'number',
        '#required' => FALSE,
        '#min' => 0,
        '#max' => 10,
        '#default_value' => 0,
        '#placeholder' => $this->t("Enter Amount of Children"),
        '#attributes' => [
          'class' => ['taxi-children'],
        ],
      ];
      $tables_config = $this->carsConfigure($form, $form_state, ++$car);
      // Recursion Goes On.
      return $tables_config;
    }
  }

  /**
   * Builds Our Taxi Form.
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#prefix'] = '<div id="form-wrapper" class="taxi-form col-md-6 col-xs-12 m-auto">';
    $form['#suffix'] = '</div>';
    // Connect library taxi_library in .libraries.yml file.
    $form['#attached']['library'][] = 'taxi/taxi_library';
    // Our Coordinates for Google Map if Ajax Rebuilds Form.
    $form['#attached']['drupalSettings']['taxi']['latitude'] = $form_state->getUserInput()['latitude'];
    $form['#attached']['drupalSettings']['taxi']['longitude'] = $form_state->getUserInput()['longitude'];
    // Set Latitude(by JS).
    $form['latitude'] = [
      '#type' => 'hidden',
      '#default_value' => '',
      '#attributes' => [
        'class' => ['taxi-latitude'],
      ],
    ];
    // Set Longitude(by JS).
    $form['longitude'] = [
      '#type' => 'hidden',
      '#default_value' => '',
      '#attributes' => [
        'class' => ['taxi-longitude'],
      ],
    ];
    // Set Name.
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#placeholder' => $this->t("Enter Your Name"),
      '#required' => TRUE,
      '#maxlength' => 100,
      '#attributes' => [
        'data-disable-refocus' => 'true',
        'autocomplete' => 'off',
        'class' => ['taxi-email'],
      ],
    ];
    // Set Email.
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#placeholder' => $this->t("Enter Your Email"),
      '#maxlength' => 100,
      '#required' => TRUE,
      '#attributes' => [
        'data-disable-refocus' => 'true',
        'autocomplete' => 'off',
        'class' => ['taxi-email'],
      ],
    ];
    // Set Date and Time.
    $form['time'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Date and Time'),
      '#size' => 20,
      '#required' => TRUE,
      '#date_date_format' => 'd/m/Y',
      '#date_time_format' => 'H:m',
      '#attributes' => [
        'class' => ['taxi-time'],
      ],
    ];
    // Button for Adding a Naw Car.
    $form['addcar'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Car'),
      '#limit_validation_errors' => [],
      '#submit' => ['::addCar'],
      '#ajax' => [
        'wrapper' => 'form-wrapper',
      ],
      '#attributes' => [
        'class' => ['taxi-add', 'btn-outline-warning'],
      ],
    ];
    // Button for Removing a Car.
    $form['removecar'] = [
      '#type' => 'submit',
      '#value' => $this->t('Remove Car'),
      '#limit_validation_errors' => [],
      '#submit' => ['::removeCar'],
      '#ajax' => [
        'wrapper' => 'form-wrapper',
      ],
      '#attributes' => [
        'class' => ['taxi-remove', 'btn-outline-warning'],
      ],
    ];
    // Calls to Function that's Builds Cars.
    // There's a variable 'car' - it's counter for tables. Start from 0.
    $this->carsConfigure($form, $form_state, 0);
    // Set Road Choose: To/From Airport.
    $form['road'] = [
      '#type' => 'select',
      '#title' => $this->t("To/From Airport"),
      '#required' => TRUE,
      '#options' => [
        'To' => $this->t('To'),
        'From' => $this->t('From'),
      ],
      '#attributes' => [
        'class' => ['taxi-road'],
      ],
    ];
    // Set Type of Tariff.
    $form['tariff'] = [
      '#type' => 'select',
      '#title' => $this->t("Your Tariff"),
      '#required' => FALSE,
      '#options' => [
        'Eco' => $this->t('Eco'),
        'Fast' => $this->t('Fast'),
        'Super-Fast' => $this->t('Super Fast'),
      ],
      '#attributes' => [
        'class' => ['taxi-road'],
      ],
    ];
    $form['map'] = [
      '#type' => 'markup',
      '#markup' =>
      '<div class="map-container">
           <div id="map" class="map" style="width: 100%;"></div>
         <div class="location-button btn btn-outline-warning" id="routebtn">Get Location</div>
         </div>',
    ];
    $form['notes'] = [
      '#title' => $this->t('Special Notes'),
      '#type' => 'textarea',
      '#placeholder' => $this->t("If You've Got Something to Say"),
      '#required' => FALSE,
      '#maxlength' => 1023,
      '#resizable' => 'none',
      '#attributes' => [
        'data-disable-refocus' => 'true',
        'autocomplete' => 'off',
        'class' => [
          'taxi-notes',
        ],
        'height' => 200,
      ],
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Order Now'),
      '#attributes' => [
        'class' => ['btn-more', 'btn-outline-warning', 'taxi-submit'],
      ],
      '#ajax' => [
        'event' => 'click',
        'effect' => 'fade',
        'wrapper' => 'form-wrapper',
        'callback' => '::submitAjax',
      ],
    ];
    return $form;
  }

  /**
   * Increases Amount of Rows and Rebuilds Our Form.
   *
   * @param array $form
   *   Our Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   It's Form State.
   *
   * @return array
   *   Returns Form.
   */
  public function addCar(array &$form, FormStateInterface $form_state): array {
    // Getting Current Amount of Rows and Increase it.
    $this->cars++;
    // Sent Out Form to Rebuild.
    $form_state->setRebuild();
    return $form;
  }

  /**
   * Increases Amount of Rows and Rebuilds Our Form.
   *
   * @param array $form
   *   Our Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   It's Form State.
   *      * //   * @return array      * //   *   Returns Form.
   */
  public function removeCar(array &$form, FormStateInterface $form_state) {
    // Getting Current Amount of Rows and Decrease it.
    // Only if More Than One Car.
    if ($this->cars > 0) {
      $this->cars--;
      // Sent Out Form to Rebuild.
      $form_state->setRebuild();
    }
    else {
      $form_state->setError($form, "Taxi: You Can't Book a Taxi Without Taxi Car");
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getPassengersAmount(FormStateInterface $form_state):array {
    $data = [];
    // For Collecting Adults.
    $data['adults'] = 0;
    // For Collecting Children.
    $data['children'] = 0;
    // Collects All Form Values.
    $form_values = $form_state->getValues();
    // Going Through Cars.
    for ($i = 0; $i < $this->cars; $i++) {
      // Collect All Adults.
      $data['adults'] += (int) $form_values['adults' . $i];
      // Collect All Children.
      $data['children'] += (int) $form_values['children' . $i];
    }
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // Collects All Form Values.
    $form_values = $form_state->getValues();
    // Get Name.
    $name = $form_state->getValue('name');
    // Get Name Length.
    $length_name = strlen($name);
    // Get Notes.
    $notes = $form_state->getValue('notes');
    // Get Notes Length.
    $length_notes = strlen($notes);
    // RegEX for Checking Name Validation.
    $requires_name = "/[-'A-Za-z ]/";
    // Get Email.
    $email = $form_state->getValue('email');
    // Get Road (To/From the Airport).
    $road = $form_state->getValue('road');
    // RegEX for Checking Email Validation.
    $requires_email = '/\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,6}/';
    // Get Time Value.
    $timer = $form_state->getValue('time');
    // Converts Time into Timstamp.
    $time = (gettype($timer) == 'object') ? strtotime($timer) : FALSE;
    // Current Timestamp for Checking Time.
    $timestamp = time();
    // Get Passengers Amount.
    $passengers = $this->getPassengersAmount($form_state);
    // We Don't Need an Empty Taxi.
    if ($passengers['adults'] == 0 && $passengers['children'] == 0) {
      $form_state->setErrorByName(
        // Choosing Field To Which Will Reference to Error.
        'adults0',
        $this->t(
          "Taxi: You Can't Book an Empty Taxi(."
        )
      );
    }
    for ($i = 0; $i < $this->cars; $i++) {
      // Child Can't Ride in Taxi Alone.
      if ((int) $form_values['adults' . $i] == 0 && (int) $form_values['children' . $i] != 0) {
        // Set Error.
        $form_state->setErrorByName(
          'children' . $i,
          'Taxi: You Can’t Let a Child Go Alone in Car №' . $i + 1 . '(.'
        );
      }
      $isintad = is_int((int) $form_values['adults' . $i]);
      $isintch = is_int((int) $form_values['children' . $i]);
      if (!$isintad || !$isintch) {
        $form_state->setErrorByName(
          'adults' . $i,
          'Taxi: You Entered Not Number Value in Car №' . $i + 1 . '(.'
        );
      }
      if (str_starts_with($form_values['children' . $i], '0') && $form_values['children' . $i] != '0') {
        $form_state->setErrorByName(
          'children' . $i,
          'Children: You Can`t Start with 0 in Car №' . $i + 1 . '(.'
        );
      }
      if (str_starts_with($form_values['adults' . $i], '0') && $form_values['adults' . $i] != '0') {
        $form_state->setErrorByName(
          'adults' . $i,
          'Adults: You Can`t Start with 0 in Car №' . $i + 1 . '(.'
        );
      }
    }
    // What a Taxi Request without A Car?
    if ($this->cars <= 0) {
      $form_state->setErrorByName(
        'removecar',
        $this->t(
          "Taxi: You Can't Book a Taxi Without Taxi Car"
        )
      );
    }
    // Invalid Time(Requested Time is in the Past).
    if ($time < $timestamp) {
      $form_state->setErrorByName(
        'time',
        $this->t(
          "Time: You Cannot Book a Taxi in the Past(."
        )
      );
    }
    // Invalid Time
    // (Difference Between Requested Time and Present is Less than 30 min).
    // PS: Only if You Going To the Airport.
    if ($road == 'To' && ($time - $timestamp < 30 * 60)) {
      $form_state->setErrorByName(
        'time',
        $this->t(
          'Time: The Difference Should Be at Least 30 Minutes if You Going To the Airport.
             Please, Give Our Driver Time to Get to You (.'
        )
      );
    }
    // Invalid Time
    // (Difference Between Requested Time and Present
    // Should be Less than 30 days).
    if ($time - $timestamp > 60 * 60 * 24 * 30) {
      $form_state->setErrorByName(
        'time',
        $this->t(
          'Time: The Limit for Ordering a Taxi in Advance is 30 days, No More(.'
        )
      );
    }
    // Invalid Name(Short Symbols).
    if ($length_name < 2) {
      $form_state->setErrorByName(
        'name',
        $this->t(
          "Name: Oh No! Your Name is Shorter Than 2 Symbols(. Don't be just Like Anonymous."
        )
      );
    }
    // Invalid Name(Too Long).
    elseif ($length_name > 100) {
      $form_state->setErrorByName(
        'name',
        $this->t(
          'Name: Oh No! Your Name is Longer Than 100 Symbols(. Can You Cut it a Bit?'
        )
      );
    }
    // Invalid Name(False Symbols).
    if (!preg_match($requires_name, $name)) {
      $form_state->setErrorByName('name',
        $this->t(
          "Name: Oh No! In Your Name %title You False Symbols(. Acceptable is: A-Z, 0-9 _ and '.", [
            '%title' => $name,
          ]
        )
      );
    }
    // Invalid Email.
    if (!preg_match($requires_email, $email)) {
      $form_state->setErrorByName('email',
        $this->t(
          'Mail: Oh No! Your Email %title is Invalid(', ['%title' => $email]
        )
      );
    }
    if ($length_notes > 1023) {
      $form_state->setErrorByName('notes', $this->t(
        'Special Notes: On No, Your Review is too Long. MaxLength - 1023. Please, Cut it Off. Your Length: %length.',
        ['%length' => $length_notes]));
    }
  }

  /**
   * AJAX validation and confirmation of the form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array|\Drupal\core\Ajax\AjaxResponse
   *   Return form or Ajax response.
   */
  public function submitAjax(array $form, FormStateInterface $form_state) {
    // If We Haven't Errors, than We Refresh Page.
    if (!$form_state->hasAnyErrors()) {
      // Creating Ajax Redirect Response.
      $response = new AjaxResponse();
      // Redirect by URI from .routing.yml file.
      $response->addCommand(new RedirectCommand('taxi'));
      return $response;
    }
    // Else Send to Validate.
    return $form;
  }

  /**
   * Submits Form.
   *
   * @throws \Exception
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Get Passengers Amount.
    $passengers = $this->getPassengersAmount($form_state);

    // Results of Our Form.
    $request['name'] = $form_state->getValue('name');
    $request['email'] = $form_state->getValue('email');
    // Convert Time Object into Timestamp.
    $request['time'] = strtotime($form_state->getValue('time'));
    $request['road'] = $form_state->getValue('road');
    $request['cars'] = $this->cars;
    $request['tariff'] = $form_state->getValue('tariff');
    $request['children'] = $passengers['children'];
    $request['adults'] = $passengers['adults'];
    $request['notes'] = $form_state->getValue('notes');
    $request['latitude'] = $form_state->getValue('latitude');
    $request['longitude'] = $form_state->getValue('longitude');

    // Array to Transfer Data for DB and Email Manager.
    $data = [
      'name' => $request['name'],
      'email' => $request['email'],
      'time' => $request['time'],
      // Amount of Cars.
      'car' => $this->cars,
      'adults' => $passengers['adults'],
      'children' => $passengers['children'],
      'road' => $request['road'],
      'tariff' => $request['tariff'],
      'latitude' => $request['latitude'],
      'longitude' => $request['longitude'],
      'notes' => $request['notes'],
      // Time of Submitting Form.
      'timestamp' => time(),
    ];

    // Pushes into DB (Taxi Table).
    \Drupal::database()->insert('taxi')->fields($data)->execute();
    // Get an Users With Role = Taxist from DB Using Entity.
    $taxis_user_ids = array_values(\Drupal::entityQuery('user')
      ->condition('roles', 'Taxist', 'CONTAINS')
      ->execute());
    // Taxis Emails.
    $taxis_email = [];
    // Taxis Name.
    $taxis_name = [];
    // Going Through theirs IDs To get An Emails.
    for ($i = 0; $i < count($taxis_user_ids); $i++) {
      $taxis_account[] = User::load($taxis_user_ids[$i]);
      $taxis_email[] = $taxis_account[$i]->getEmail();
      $taxis_name[] = $taxis_account[$i]->getAccountName();
    }
    // Selecting Taxis Through Random.
    $taxis_num = rand(0, count($taxis_email));
    // Sends an Email.
    // Calls to Drupal Plugin that is Responsible for Sendings Emails.
    $newMail = \Drupal::service('plugin.manager.mail');
    // Email Client.
    // Params: module_name, Key, Email to Send,
    // Language, Data, Reply Available and Send Status.
    $mail = $newMail
      ->mail('taxi', 'ordered', $request['email'], 'en', $request, NULL, TRUE);
    // Taxis Email.
    $request['taxis'] = ucwords($taxis_name[$taxis_num]);
    $taxis_email = $newMail
      ->mail('taxi', 'job', $taxis_email[$taxis_num], 'en', $request, NULL, TRUE);
    // Success Message.
    $this->messenger()
      ->addStatus($this
        ->t('You Booked a Taxi on %time. Wait Until ' . ucwords($taxis_name[$taxis_num]) . '(' . $taxis_email[$taxis_num] . ') Driver Contacts You', [
          '%time' => $form_state->getValue('time'),
        ]));
  }

}
