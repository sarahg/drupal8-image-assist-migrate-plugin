<?php

/**
 * @file
 * Contains \Drupal\nama_migrate\Plugin\migrate\process\NamaImgAssist.
 */

namespace Drupal\nama_migrate\Plugin\migrate\process;

use Drupal\Core\Database\Database;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * This plugin replaces img_assist tags in node body fields with standard HTML image tags.
 *
 * @MigrateProcessPlugin(
 *   id = "nama_img_assist"
 * )
 */
class NamaImgAssist extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function findImgAssistTags($value) {
    $pattern = "/\[img_assist(?:\\\\|\\\]|[^\]])*\]/"; // See http://rubular.com/r/gQs5HjGLok
    preg_match($pattern, $value, $matches, PREG_OFFSET_CAPTURE); // The PREG_OFFSET_CAPTURE gives us the offset_in_tmp variable.
    return $matches;
  }

  /**
   * {@inheritdoc}
   */
  protected function replaceImgAssistTags($value) {
    $matches = self::findImgAssistTags($value);

    foreach ($matches as $image_marker) {
      list($img, $offset_in_tmp) = $image_marker;

      // Strip off the first and last character - they are [ and ].
      $img_pieces = preg_replace("/^\[(.*)\]$/", '${1}', $img);

      // Break the img_assist string into useful bits.
      // The dollar-underscore variable is a junk collector.
      list($_, $nid, $title, $desc, $link, $url, $align, $width, $height) = explode("|", $img_pieces);

      list($_, $nid) = explode('=', $nid, 2);
      list($_, $title) = explode('=', $title, 2);
      list($_, $desc) = explode('=', $desc, 2);
      list($_, $link) = explode('=', $link, 2);
      list($_, $url) = explode('=', $url, 2);
      list($_, $align) = explode('=', $align, 2);
      list($_, $width) = explode('=', $width, 2);
      list($_, $height) = explode('=', $height, 2);

      // Retrieve the image path from the image node.
      $image_path = self::getImagePath($nid);
      $image_tag = "<img alt=\"$desc\" src=\"$image_path\" style=\"width: ". $width ."px; height: ". $height ."px;\">";

      // Add the link if it exists.
      if ($link && $url) {
        $image_tag = '<a href="'. $url .'">' . $image_tag . '</a>';
      }

      $value = str_replace($img, $image_tag, $value);
    }

    return $value;
  }

  /**
   * {@inheritdoc}
   */
  private function getImagePath($nid) {
    // Look up the node referenced by the img_assist tag, then grab the image file ID from that node.
    $query_image = Database::getConnection('default', 'migrate')->query('SELECT * FROM {image} WHERE nid=:nid', array(':nid' => $nid));

    // Get the image path from the image file ID.
    foreach ($query_image as $image) {
      $query_file = Database::getConnection('default', 'migrate')->query('SELECT * FROM {files} WHERE fid=:fid', array(':fid' => $image->fid));

      foreach ($query_file as $file) {
        // Add a slash at the beginning of the path.
        $image_path = '/' . $file->filepath;
      }
    }

    return $image_path;
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return $this->replaceImgAssistTags($value);
  }
}
