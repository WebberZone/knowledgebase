/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import { bookIcon } from '../components/icons';
import Edit from './edit';
import metadata from './block.json';
import './editor.scss';
import './style.scss';

/**
 * Register the block
 */
registerBlockType(metadata.name, {
	...metadata,
	icon: bookIcon,
	edit: Edit,
});
