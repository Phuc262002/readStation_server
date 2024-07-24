<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookDetail;
use App\Models\BookReviews;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

use function PHPSTORM_META\map;

#[OA\Get(
    path: '/api/v1/public/books',
    tags: ['Public / Book'],
    operationId: 'getAllBooksPublic',
    summary: 'Get all books public',
    description: 'Get all books',
    parameters: [
        new OA\Parameter(
            name: 'page',
            in: 'query',
            required: false,
            description: 'Số trang hiện tại',
            schema: new OA\Schema(type: 'integer', default: 1)
        ),
        new OA\Parameter(
            name: 'pageSize',
            in: 'query',
            required: false,
            description: 'Số lượng mục trên mỗi trang',
            schema: new OA\Schema(type: 'integer', default: 10)
        ),
        new OA\Parameter(
            name: 'search',
            in: 'query',
            required: false,
            description: 'Từ khóa tìm kiếm',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'category_id',
            in: 'query',
            required: false,
            description: 'Id của category',
            schema: new OA\Schema(type: 'integer')
        ),
        new OA\Parameter(
            name: 'author_id',
            in: 'query',
            required: false,
            description: 'Id của author',
            schema: new OA\Schema(type: 'integer')
        ),
        new OA\Parameter(
            name: 'publishing_company_id',
            in: 'query',
            required: false,
            description: 'Id của publishing company',
            schema: new OA\Schema(type: 'integer')
        ),
        new OA\Parameter(
            name: 'sort',
            in: 'query',
            required: false,
            description: 'Sắp xếp theo thứ tự',
            schema: new OA\Schema(type: 'string', enum: ['asc', 'desc', 'popular'], default: 'asc')
        ),
        new OA\Parameter(
            name: 'rating',
            in: 'query',
            required: false,
            description: 'Đánh giá sách',
            schema: new OA\Schema(type: 'integer', enum: [1, 2, 3, 4, 5])
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all books successfully!',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
    ],
)]

#[OA\Get(
    path: '/api/v1/public/books/{slug}',
    tags: ['Public / Book'],
    operationId: 'getOneBook',
    summary: 'Get one book by slug',
    description: 'Get one book',
    parameters: [
        new OA\Parameter(
            name: 'slug',
            in: 'path',
            required: true,
            description: 'Slug của sách',
            schema: new OA\Schema(type: 'string')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get book successfully!',
        ),
        new OA\Response(
            response: 404,
            description: 'Book not found!',
        ),
    ],
)]

class BookController extends Controller
{
    public function checkBookDetail()
    {
        // Find books without a bookDetail or with an inactive bookDetail
        $booksWithoutDetail = Book::doesntHave('bookDetail')
            ->orWhereHas('bookDetail', function ($q) {
                $q->where('status', '!=', 'active');
            })
            ->get();

        // Update the status of these books to 'needUpdateDetail'
        $booksWithoutDetail->each(function ($book) {
            if ($book->status != 'needUpdateDetail') {
                $book->update(['status' => 'needUpdateDetail']);
            }
        });
    }

    public function index(Request $request)
    {
        $this->checkBookDetail();

        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'search' => 'string',
            'category_id' => 'integer',
            'author_id' => 'integer',
            'publishing_company_id' => 'integer',
            'rating' => 'integer',
            'sort' => 'string|in:asc,desc,popular',
        ], [
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'pageSize.integer' => 'Kích thước trang phải là số nguyên.',
            'pageSize.min' => 'Kích thước trang phải lớn hơn hoặc bằng 1.',
            'category_id.integer' => 'Category_id phải là một số nguyên.',
            'author_id.integer' => 'Author_id phải là một số nguyên.',
            'publishing_company_id.integer' => 'Publishing_company_id phải là một số nguyên.',
            'sort.string' => 'Sort phải là một chuỗi.',
            'sort.in' => 'Sort phải là một trong các giá trị: asc, desc, popular.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        // Lấy giá trị page và pageSize từ query parameters
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);
        $search = $request->input('search');
        $category_id = $request->input('category_id');
        $author_id = $request->input('author_id');
        $publishing_company_id = $request->input('publishing_company_id');
        $rating = $request->input('rating');
        $sort = $request->input('sort', 'asc');

        // Tạo query ban đầu
        $query = BookDetail::with([
            'publishingCompany',
            'order_details',
            'book.category',
            'book.author',
            'book.shelve',
            'book.shelve.bookcase',
            'book.shelve.category',
            'book'
        ])
            ->whereHas('book', function ($query) {
                $query->where('status', 'active');
            })
            ->where('status', 'active');


        $totalItems = $query->count();

        if ($sort == 'popular') {
            $query = $query->groupBy('id')->orderByRaw('COUNT(id) DESC');
        } else {
            $query = $query->orderBy('publish_date', $sort);
        }

        $query = $query->filter($category_id, $author_id, $publishing_company_id);

        // Áp dụng bộ lọc tìm kiếm nếu có tham số tìm kiếm
        $query = $query->search($search);

        // Thực hiện phân trang
        $books = $query->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

        $filteredBooks = $books->getCollection()->filter(function ($book) use ($rating) {
            $bookReviews = BookReviews::where('book_details_id', $book->id);;
            $averageRateRounded = round($bookReviews->avg('rating'), 1);

            $book->average_rate = $averageRateRounded;
            $book->rating_total = $bookReviews->count();
            $book->hire_count = $book->order_details->count();
            unset($book->order_details);

            if ($rating) {
                return $averageRateRounded >= $rating && $averageRateRounded <= $rating + 1;
            }

            return true; // Nếu không có rating, giữ lại tất cả các sách
        });

        // Cập nhật lại bộ sưu tập với những sách đã lọc
        $books->setCollection($filteredBooks);


        return response()->json([
            "status" => true,
            "message" => "Get all books successfully!",
            "data" => [
                "books" => $books->items(),
                "page" => $books->currentPage(),
                "pageSize" => $books->perPage(),
                "totalPages" => $books->lastPage(),
                "totalResults" => $books->total(),
                "total" => $totalItems
            ],
        ], 200);
    }

    public function show(Request $request)
    {
        $this->checkBookDetail();
        $slug = $request->route('slug');
        // Tách chuỗi bằng dấu '-'
        $parts = explode('-', $slug);
        // Lấy phần cuối cùng
        $book_detail_id = array_pop($parts);
        // Ghép lại phần đầu tiên
        $slug_book = implode('-', $parts);

        $validator = Validator::make([
            'slug_book' => $slug_book,
            'book_detail_id' => $book_detail_id
        ], [
            'slug_book' => 'required|exists:books,slug',
            'book_detail_id' => 'required|integer|exists:book_details,id'
        ], [
            'slug_book.required' => 'Slug của sách không được để trống.',
            'slug_book.exists' => 'Slug của sách không tồn tại.',
            'book_detail_id.required' => 'Id của book detail không được để trống.',
            'book_detail_id.exists' => 'Id của book detail không tồn tại.',
            'book_detail_id.integer' => 'Id của book detail phải là một số nguyên.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $checkSlug = Book::with('bookDetail')->where('slug', $slug_book)->first();

        foreach ($checkSlug->bookDetail as $bookDetail) {
            if ($bookDetail->id == $book_detail_id) {
                $checkSlug = true;
                break;
            } else {
                $checkSlug = false;
            }
        }

        if (!$checkSlug) {
            return response()->json([
                "status" => false,
                "message" => "Book not found!"
            ], 404);
        }

        $book = BookDetail::with([
            'publishingCompany',
            'order_details',
            'book.category',
            'book.author',
            'book.shelve',
            'book.shelve.bookcase',
        ])
            ->where('id', $book_detail_id)
            ->first();

        $bookReviews = BookReviews::with('user')->where('book_details_id', $book_detail_id);
        $averageRateRounded = round($bookReviews->avg('rating'), 1);

        $book->average_rate = $averageRateRounded;
        $book->rating_total = $bookReviews->count();

        $rating_comments = $bookReviews->get()->map(function ($review) {
            return array_merge(
                $review->only(
                    ['id', 'rating', 'review_text', 'review_date'],
                ),
                ['user' => $review->user->only(['id', 'fullname', 'avatar'])]
            );
        });

        $book->order_count = $book->order_details->where('status_od', 'completed')->count();
        $book->rating_comments = $rating_comments;

        unset($book->order_details);


        if (!$book) {
            return response()->json([
                "status" => false,
                "message" => "Book not found!"
            ], 404);
        } elseif ($book->status != 'active') {
            return response()->json([
                "status" => false,
                "message" => "Book is not active!"
            ], 404);
        }

        return response()->json([
            "status" => true,
            "message" => "Get book successfully!",
            "data" => $book
        ], 200);
    }
}
