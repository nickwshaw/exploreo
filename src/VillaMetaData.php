<?php

namespace Exploreo;

class VillaMetaData
{

    public const META_KEY_CHECKSUM = 'villa_checksum';
    public const META_KEY_UPDATE_VERSION = 'villa_update_version';

    /**
     * List of houses
     */
    public const META_KEY_HOUSE_CODE = 'house_code';
    public const API_KEY_HOUSE_CODE = 'HouseCode';
    public const API_REAL_OR_TEST = 'RealOrTest';

    /**
     * BasicInformation
     */
    public const META_KEY_MAX_NUMBER_OF_PERSONS = 'villa_max_number_of_persons';
    public const META_KEY_EXCEED_MAX_NUMBER_OF_BABIES = 'villa_exceed_max_number_of_babies';
    public const META_KEY_MAX_NUMBER_OF_PETS = 'villa_max_number_of_pets';
    public const META_KEY_NUMBER_OF_BEDROOMS = 'villa_number_of_bedrooms';
    public const META_KEY_NUMBER_OF_BATHROOMS = 'villa_number_of_bathrooms';
    public const META_KEY_ARRIVAL_TIME_FROM = 'villa_arrival_time_from';
    public const META_KEY_ARRIVAL_TIME_UNTIL = 'villa_arrival_time_until';
    public const META_KEY_DEPARTURE_TIME_FROM = 'villa_departure_time_from';
    public const META_KEY_DEPARTURE_TIME_UNTIL = 'villa_departure_time_until';
    public const META_KEY_PROPERTIES = 'villa_properties';
    public const META_KEY_DISTANCES = 'villa_distances';
    public const META_KEY_NUMBER_OF_STARS = 'villa_number_of_stars';
    public const META_KEY_DIMENSION_M2 = 'villa_dimension_m2';
    public const META_KEY_CITY = 'villa_city';
    public const META_KEY_CITY_SLUG = 'villa_city_slug';
    public const META_KEY_COUNTRY = 'villa_country';
    public const META_KEY_COUNTRY_SLUG = 'villa_country_slug';
    public const META_KEY_PROVINCE = 'villa_province';
    public const META_KEY_PROVINCE_SLUG = 'villa_province_slug';
    public const META_KEY_LATITUDE = 'villa_latitude';
    public const META_KEY_LONGITUDE = 'villa_longitude';
    public const META_KEY_HOUSE_TYPE = 'villa_house_type';

    public const API_KEY_NAME = 'Name';
    public const API_KEY_MAX_NUMBER_OF_PERSONS = 'MaxNumberOfPersons';
    public const API_KEY_EXCEED_MAX_NUMBER_OF_BABIES = 'ExceedMaxNumberOfBabies';
    public const API_KEY_MAX_NUMBER_OF_PETS = 'MaxNumberOfPets';
    public const API_KEY_NUMBER_OF_BEDROOMS = 'NumberOfBedrooms';
    public const API_KEY_NUMBER_OF_BATHROOMS = 'NumberOfBathrooms';
    public const API_KEY_ARRIVAL_TIME_FROM = 'ArrivalTimeFrom';
    public const API_KEY_ARRIVAL_TIME_UNTIL = 'ArrivalTimeUntil';
    public const API_KEY_DEPARTURE_TIME_FROM = 'DepartureTimeFrom';
    public const API_KEY_DEPARTURE_TIME_UNTIL = 'DepartureTimeUntil';
    public const API_KEY_PROPERTIES = 'Properties';
    public const API_KEY_DISTANCES = 'Distances';
    public const API_KEY_NUMBER_OF_STARS = 'NumberOfStars';
    public const API_KEY_DIMENSION_M2 = 'DimensionM2';
    public const API_KEY_CITY = 'City';
    public const API_KEY_COUNTRY = 'Country';
    public const API_KEY_PROVINCE = 'Province';
    public const API_KEY_LATITUDE = 'Latitude';
    public const API_KEY_LONGITUDE = 'Longitude';
    public const API_KEY_HOUSE_TYPE = 'HouseType';

    /**
     * Description
     */
    public const META_KEY_REMARKS = 'villa_remarks';
    public const META_KEY_LAYOUT = 'villa_layout';
    public const META_KEY_DESCRIPTION_TITLE = 'villa_description_title';
    public const META_KEY_DESCRIPTION = 'villa_description';

    public const API_KEY_REMARKS = 'Remarks';
    public const API_KEY_LAYOUT = 'Layout';
    public const API_KEY_DESCRIPTION_TITLE = 'Title';
    public const API_KEY_DESCRIPTION = 'Description';
    public const API_KEY_LANGUAGE = 'Language';

    /**
     * Media
     */
    public const META_KEY_MEDIA_PHOTOS = 'villa_photos';
    public const API_KEY_MEDIA_PHOTOS = 'TypeContents';

    /**
     * API methods
     */

    public const API_METHOD_MEDIA = 'MediaV1';
    public const API_METHOD_DESCRIPTION = 'DescriptionV1';
    public const API_METHOD_BASIC_INFORMATION = 'BasicInformationV1';

    /**
     * @return string[]
     */
    public static function getBasicInformationMetaDataKeys(): array
    {
        return [
            self::API_KEY_MAX_NUMBER_OF_PERSONS => self::META_KEY_MAX_NUMBER_OF_PERSONS,
            self::API_KEY_EXCEED_MAX_NUMBER_OF_BABIES => self::META_KEY_EXCEED_MAX_NUMBER_OF_BABIES,
            self::API_KEY_MAX_NUMBER_OF_PETS => self::META_KEY_MAX_NUMBER_OF_PETS,
            self::API_KEY_NUMBER_OF_BEDROOMS => self::META_KEY_NUMBER_OF_BEDROOMS,
            self::API_KEY_NUMBER_OF_BATHROOMS => self::META_KEY_NUMBER_OF_BATHROOMS,
            self::API_KEY_ARRIVAL_TIME_FROM => self::META_KEY_ARRIVAL_TIME_FROM,
            self::API_KEY_ARRIVAL_TIME_UNTIL => self::META_KEY_ARRIVAL_TIME_UNTIL,
            self::API_KEY_DEPARTURE_TIME_FROM => self::META_KEY_DEPARTURE_TIME_FROM,
            self::API_KEY_DEPARTURE_TIME_UNTIL => self::META_KEY_DEPARTURE_TIME_UNTIL,
            self::API_KEY_PROPERTIES => self::META_KEY_PROPERTIES,
            self::API_KEY_DISTANCES => self::META_KEY_DISTANCES,
            self::API_KEY_NUMBER_OF_STARS => self::META_KEY_NUMBER_OF_STARS,
            self::API_KEY_DIMENSION_M2 => self::META_KEY_DIMENSION_M2,
            self::API_KEY_CITY => self::META_KEY_CITY,
            self::API_KEY_COUNTRY => self::META_KEY_COUNTRY,
            self::API_KEY_PROVINCE => self::META_KEY_PROVINCE,
            self::API_KEY_LATITUDE => self::META_KEY_LATITUDE,
            self::API_KEY_LONGITUDE => self::META_KEY_LONGITUDE,
        ];
    }

    public static function getMetaDataKeysForAlgoliaImport()
    {
        return [
            self::META_KEY_CITY,
            self::META_KEY_COUNTRY,
            self::META_KEY_PROVINCE,
            self::META_KEY_MAX_NUMBER_OF_PERSONS,
            self::META_KEY_EXCEED_MAX_NUMBER_OF_BABIES,
            self::META_KEY_MAX_NUMBER_OF_PETS,
            self::META_KEY_NUMBER_OF_BEDROOMS,
            self::META_KEY_NUMBER_OF_BATHROOMS,
            self::META_KEY_NUMBER_OF_STARS,
        ];
    }

}
