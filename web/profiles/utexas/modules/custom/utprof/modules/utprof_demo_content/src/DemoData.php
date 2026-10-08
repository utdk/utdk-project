<?php

namespace Drupal\utprof_demo_content;

/**
 * PHP array of utprof node data.
 */
class DemoData {

  /**
   * {@inheritdoc}
   */
  public static function loadData() {
    $data = [
      'Minimal profile' => [
        'title' => 'Sample Profile (minimal content)',
        'profile_groups' => [
          'Faculty',
          'Staff',
        ],
        'fields' => [
          'field_utprof_given_name' => 'Sample',
          'field_utprof_surname' => 'Content',
        ],
      ],
      'Maximal profile' => [
        'title' => 'Sample Profile (maximal content)',
        'profile_groups' => [
          'Leadership',
        ],
        'basic_media' => [
          'asset' => 'rodin-young-girl.jpg',
          'filename' => 'Valeria Messalina',
          'alt_text' => 'Roman statue',
          'title_text' => 'Bust of Roman statue',
        ],
        'fields' => [
          'field_utprof_add_contact_info' => [
            'value' => 'Main Building (MAI)<br />110 Inner Campus Drive<br />Austin, TX 78705',
            'format' => 'flex_html',
          ],
          'field_utprof_add_basic_info' => [
            'value' => 'The third wife of the Roman emperor Claudius, she was a paternal cousin of Emperor Nero.',
            'format' => 'flex_html',
          ],
          'field_utprof_building_code' => 'FAC',
          'field_utprof_building_room_numb' => '326T',
          'field_utprof_contact_form_link' => [
            'title' => 'Contact Us',
            'uri' => 'https://utexas.qualtrics.com/SE/?SID=SV_af1Gk9JWK2khAEJ',
          ],
          'field_utprof_content' => [
            0 => [
              'header' => 'History',
              'body_value' => "<p>Messalina was the daughter of Domitia Lepida the Younger and her first cousin Marcus Valerius Messalla Barbatus. Her mother was the youngest child of the consul Lucius Domitius Ahenobarbus and Antonia Major. Her mother's brother, Gnaeus Domitius Ahenobarbus, had been the first husband of the future Empress Agrippina the Younger and the biological father of the future Emperor Nero, making Nero Messalina's first cousin despite a seventeen-year age difference. Messalina's grandmothers Claudia Marcella and Antonia Major were half sisters. Claudia Marcella, Messalina's paternal grandmother, was the daughter of Augustus' sister Octavia the Younger by her marriage to Gaius Claudius Marcellus Minor. Image used under the <a href='https://flic.kr/p/z4EhPK'>FlickR Creative Commons</a> license.</p>",
              'body_format' => 'flex_html',
            ],
            1 => [
              'header' => 'Later Work',
              'body_value' => "<p>Little is known about Messalina's life prior to her marriage in 38 to Claudius, her first cousin once removed, who was then about 47 years old. Two children were born as a result of their union: a daughter Claudia Octavia (born 39 or 40), a future empress, stepsister and first wife to the emperor Nero; and a son, Britannicus. When the Emperor Caligula was murdered in 41, the Praetorian Guard proclaimed Claudius the new emperor and Messalina became empress.</p>",
              'body_format' => 'flex_html',
            ],
          ],
          'field_utprof_designation' => [
            0 => ['value' => 'Consul'],
            1 => ['value' => 'Procurator'],
          ],
          'field_utprof_eid' => 'vm123',
          'field_utprof_email_address' => 'valeria.messalina@bmail.com',
          'field_utprof_fax_number' => '123-456-7890',
          'field_utprof_given_name' => 'Valeria',
          'field_utprof_phone_number' => '234-567-8901',
          'field_utprof_surname' => 'Messalina',
          'field_utprof_website_link' => [
            'title' => '',
            'uri' => 'https://example.com',
          ],
        ],
      ],
      'Gnaeus Scipio Calvus' => [
        'title' => 'Gnaeus Scipio Calvus',
        'profile_groups' => [
          'Leadership',
        ],
        'basic_media' => [
          'asset' => 'general-2.jpg',
          'filename' => 'Gnaeus Scipio Calvus',
          'alt_text' => 'Roman statue',
          'title_text' => 'Bust of Roman statue',
        ],
        'fields' => [
          'field_utprof_given_name' => 'Gnaeus',
          'field_utprof_phone_number' => '234-567-8901',
          'field_utprof_surname' => 'Scipio Calvus',
          'field_utprof_content' => [
            0 => [
              'header' => 'History',
              'body_value' => '<p>His father was Lucius Cornelius Scipio, son of the patrician censor of 280, Lucius Cornelius Scipio Barbatus. His younger brother was Publius Cornelius Scipio, father of the most famous Scipio – Scipio Africanus. Gnaeus Cornelius Scipio Calvus served as consul in 222 BC, his co-consul being Marcus Claudius Marcellus, who was serving in the first of his five consulships. Gnaeus Cornelius Scipio was nicknamed Calvus (the bald) to distinguish him from his uncle, another Gnaeus Cornelius Scipio, who was nicknamed Asina (or donkey) and had twice been consul during the First Punic War. Image used under the <a href="https://flic.kr/p/z8DxzN">FlickR Creative Commons</a> license.</p>',
              'body_format' => 'flex_html',
            ],
          ],
        ],
      ],
      'Cornelia Africana' => [
        'title' => 'Cornelia Africana',
        'profile_groups' => [
          'Faculty',
        ],
        'basic_media' => [
          'asset' => 'jupiter.jpg',
          'filename' => 'Cornelia Africana',
          'alt_text' => 'Roman statue',
          'title_text' => 'Bust of Roman statue',
        ],
        'fields' => [
          'field_utprof_given_name' => 'Cornelia',
          'field_utprof_surname' => 'Africana',
          'field_utprof_content' => [
            0 => [
              'header' => 'History',
              'body_value' => '<p>Cornelia Africana was the daughter of Scipio Africanus, famous for his victory against Hannibal in the Second Punic War. She died at age 90 in 100 BC, and was remembered by the Romans as an exemplar of virtue. Image used under the <a href="https://flic.kr/p/c5nnjU">FlickR Creative Commons</a> license.</p>',
              'body_format' => 'flex_html',
            ],
          ],
        ],
      ],
      'Portia Catonis' => [
        'title' => 'Portia Catonis',
        'profile_groups' => [
          'Faculty',
          'Staff',
        ],
        'basic_media' => [
          'asset' => 'kouros.jpg',
          'filename' => 'Portia Catonis',
          'alt_text' => 'Roman statue',
          'title_text' => 'Bust of Roman statue',
        ],
        'fields' => [
          'field_utprof_given_name' => 'Portia',
          'field_utprof_surname' => 'Catonis',
          'field_utprof_content' => [
            0 => [
              'header' => 'History',
              'body_value' => '<p>Porcia lived from around 70 to 42 BC. Porcia was the daughter of Cato the Younger, but is most famous as the wife to Marcus Junius Brutus. Porcia was considered to be both kind and brave, and was a lover of philosophy. Her first marriage was to Bibulus, an ally of Cato. Image used under the <a href="https://flic.kr/p/u4QJK">FlickR Creative Commons</a> license.</p>',
              'body_format' => 'flex_html',
            ],
          ],
        ],
      ],
      'Marcus Tullius Cicero' => [
        'title' => 'Marcus Tullius Cicero',
        'profile_groups' => [
          'Faculty',
          'Staff',
        ],
        'basic_media' => [
          'asset' => 'general-1.jpg',
          'filename' => 'Marcus Tullius Cicero',
          'alt_text' => 'Roman statue',
          'title_text' => 'Bust of Roman statue',
        ],
        'fields' => [
          'field_utprof_given_name' => 'Marcus',
          'field_utprof_surname' => 'Tullius Cicero',
          'field_utprof_content' => [
            0 => [
              'header' => 'History',
              'body_value' => "<p>Marcus Tullius Cicero was a Roman philosopher, politician, lawyer, orator, political theorist, consul and constitutionalist. He came from a wealthy municipal family of the Roman equestrian order, and is widely considered one of Rome's greatest orators and prose stylists. Image used under the <a href='https://flic.kr/p/k9fy44'>FlickR Creative Commons</a> license.</p>",
              'body_format' => 'flex_html',
            ],
          ],
        ],
      ],
    ];

    return $data;
  }

}
